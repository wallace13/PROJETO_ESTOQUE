<?php

namespace App\Http\Controllers\Admin;

use App\Models\Estoque;
use App\Models\Entrada;
use App\Models\Produto;
use App\Models\Saida;
use App\Http\Controllers\Admin\EstoqueCrudController;
use App\Http\Controllers\Admin\EntradaCrudController;
use App\Http\Requests\SaidaRequest;
use Illuminate\Support\Facades\DB;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class SaidaCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    //use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Saida::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/saida');
        CRUD::setEntityNameStrings('Saída', 'Saídas');
    }

    protected function setupListOperation()
    {
        $this->setupCommonColumns();
        $this->crud->addButtonFromView('line', 'cancelarSaida', 'cancelarSaida', 'end');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(SaidaRequest::class);
        $produtos = Entrada::with('estoque.produto.ufs')->get();
        $Itens = $produtos->map(function ($produto) {
            if($produto->quantidade != $produto->qtdSaidas){
                return ['id' => $produto->id, 'name' => $produto->estoque->produto->nome.' - '.$produto->estoque->produto->ufs->uf.' - '.date('d/m/Y', strtotime($produto->validade)).' - QTD: '.($produto->quantidade-$produto->qtdSaidas)];
            }            
        })->pluck('name', 'id')->toArray();
        asort($Itens);
        CRUD::field([   // select_from_array
            'name'        => 'estoque_id',//Aqui ele pega o id da entrada
            'label'       => "Produto",
            'type'        => 'select_from_array',
            'options'     => [null => 'Escolha um produto'] +$Itens,
            'allows_null' => false,
            'default'     => 'one',
        ]);
        
        CRUD::field([  
            'label'     => "Quantidade",
            'type'      => 'text',
            'name'      => 'quantidade',
        ]);     
        CRUD::field([   
            'name'        => 'user_id',
            'label'       => "Usuario",
            'type'        => 'hidden',
            'value' => backpack_auth()->user()->id,
            'allows_null' => false,
            'default'     => 'one',
        ]); 
    }

    public function store()
    {
        DB::beginTransaction();// Inicia a transação do banco de dados
        try {
            $request = $this->crud->validateRequest();
            $idEntrada = $request->estoque_id;
            $entrada = Entrada::where('id', $idEntrada)->first();
            $estoque = Estoque::where('id', $entrada->estoque_id)->first();
            
            EstoqueCrudController::atualizarQuantidadesSaida($request, $entrada, $estoque);

            $request['estoque_id'] = $estoque->id;
            $request['entrada_id'] = $entrada->id;

            $entry = $this->crud->create($request->except(['_token', '_method'])); 

            DB::commit();// Se tudo correu bem, commit na transação
            $rota = $this->redirecionamentoRotas($request->get('_save_action'), $entry);
            return $rota;
        } catch (\Exception $e) {
            DB::rollback();// Se ocorrer uma exceção, reverta a transação
            throw $e;
        }
    }

    public function redirecionamentoRotas($saveAction,$entry){
        if ($saveAction === 'save_and_back') {
            return redirect("/admin/saida");
        } elseif ($saveAction === 'save_and_edit') {
            return redirect("/admin/saida/{$entry->id}/edit");
        } elseif ($saveAction === 'save_and_preview') {
            return redirect("/admin/saida/{$entry->id}/show");
        }elseif ($saveAction === 'save_and_new') {
            return redirect("/admin/saida/create");
        }
    }

    protected function setupUpdateOperation()
    {
        $saida = Saida::with('estoque.produto.ufs')->findOrFail($this->crud->getCurrentEntry()->id);
        CRUD::field([   // select_from_array
            'name'        => 'estoque_id_disable',//Aqui ele pega o id da entrada
            'label'       => "Produto",
            'type'        => 'select_from_array',
            'value'       => $saida->entrada_id, 
            'options'     => [$saida->estoque->produto->nome.' - '.$saida->estoque->produto->ufs->uf.' - '.date('d/m/Y', strtotime($saida->entrada->validade))],
            'allows_null' => false,
            'default'     => 'one',
            'attributes' => [
                'disabled'    => 'disabled',
            ],
        ]);  
        $this->setupCreateOperation();
        CRUD::field([
            'name'  => 'estoque_id',//Aqui ele pega o id da entrada
            'type'  => 'hidden',
            'value' => $saida->entrada_id,
        ]);


    }
    public function update()
    {
        DB::beginTransaction();// Inicia a transação do banco de dados
        try {
            $request = $this->crud->validateRequest();
            $saida = Saida::find($request->id);
            $entrada = Entrada::find($request->estoque_id);//estoque_id na real é a entrada id
            $estoque = Estoque::find($entrada->estoque_id);

            $quantidadeNova = $estoque->atualizarQuantidadeSaida($request->quantidade, $saida->quantidade);
            
            $validades = $estoque->decodeValidadesJSON($estoque->validades);
            
            $indiceItem = $estoque->buscaValidadeNoArray($entrada->validade, $validades);
            if($quantidadeNova['total'] == 0){
                $qtdValidadesEntrada = $estoque->countValidadeEntrada($entrada);
                if ($qtdValidadesEntrada <= 1) {
                    $validades = $estoque->removeValidade($indiceItem, $validades);
                }
            }else if(intval($request->quantidade) != $entrada->qtdSaidas){
                if ($indiceItem === false) {
                    $validades[] = $estoque->criaArrayValidades($entrada->validade);
                }
            }

            if (intval($request->quantidade) < $saida->quantidade) {
                $subtotal = $saida->quantidade - intval($request->quantidade);
                $qtdSaidas = $entrada->qtdSaidas - $subtotal;
            } else {
                $qtdSaidas = $entrada->qtdSaidas+(intval($request->quantidade) - $saida->quantidade);
            }
           
            $saida->update(['quantidade' => intval($request->quantidade)]);
            $entrada->update(['qtdSaidas' => $qtdSaidas]);
            $estoque->update(['qtdTotal' => $quantidadeNova['qtdNova'], 'validades' => json_encode($validades)]);

            DB::commit();// Se tudo correu bem, commit na transação
            $rota = $this->redirecionamentoRotas($request->get('_save_action'), $request);
            return $rota;
        } catch (\Exception $e) {
            DB::rollback();// Se ocorrer uma exceção, reverta a transação
            throw $e;
        }
    }
    protected function setupShowOperation()
    {
        $this->setupCommonColumns();
        CRUD::addColumn([
            'name' => 'user_id',
            'label' => 'Criado por',
            'type' => 'text', 
            'value' => function ($entry) {
                $user = Saida::with('users')->findOrFail($entry->id);
                if ($user) {
                    return $user->users->name; 
                }
                return 'Usuário não encontrada';
            },
        ]);
        CRUD::addColumn([
            'name' => 'created_at',
            'label' => 'Criado em',
            'type' => 'text', 
            'value' => function ($entry) {
                return date('d/m/Y H:i', strtotime($entry->created_at));
            },
        ]);
        CRUD::addColumn([
            'name' => 'updated_at',
            'label' => 'Última Edição',
            'type' => 'text', 
            'value' => function ($entry) {
                return date('d/m/Y H:i', strtotime($entry->updated_at));
            },
        ]);
    }
    protected function setupCommonColumns()
    {
        CRUD::addColumn([
            'name' => 'estoque_id',
            'label' => 'Produto',
            'type' => 'text', 
            'value' => function($entry) {
                $saida = Saida::with('estoque.produto.ufs')->findOrFail($entry->id);
                if ($saida) {
                    return $saida->estoque->produto->nome; 
                }
                return 'Produto não encontrado no estoque';
            },
            'searchLogic'    => true,
            'orderable'      => true,
            'visibleInModal' => true,
        ]);
        CRUD::addColumn([
            'name' => 'uf',
            'label' => 'Uf',
            'type' => 'text',
            'value' => function($entry) {
                $saida = Saida::with('estoque.produto.ufs')->findOrFail($entry->id);
                if ($saida) {
                    return $saida->estoque->produto->ufs->uf; 
                }
                return 'Uf do Produto não encontrada';
            },
            'searchLogic'    => true,
            'orderable'      => true,
            'visibleInModal' => true,
        ]);
        CRUD::addColumn([
            'name' => 'quantidade',
            'label' => 'Quantidade',
            'type' => 'text',
            'searchLogic'    => true,
            'orderable'      => true,
            'visibleInModal' => true,
        ]);
    }
    public function cancelarSaida($id)
    {
        DB::beginTransaction();
        try {
            $saida = Saida::find($id);
            $entrada = Entrada::find($saida->entrada_id);
            $estoque = Estoque::find($saida->estoque_id);
        
            EstoqueCrudController::updateValidades($entrada, $estoque,null);
            //Estoque/ Entrada/ Saida
            EstoqueCrudController::removeQuantidade($estoque, null, $saida);
            EntradaCrudController::updateQuantidade($entrada,$saida);

            $saida->delete();
            DB::commit();// Se tudo correu bem, commit na transação
            \Alert::success("Saida cancelada com sucesso")->flash();
            return redirect("/admin/saida");
        } catch (\Exception $e) {
            DB::rollback();// Se ocorrer uma exceção, reverta a transação
            throw $e;
        }
    }
}

