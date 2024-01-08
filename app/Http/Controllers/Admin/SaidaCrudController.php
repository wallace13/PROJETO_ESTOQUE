<?php

namespace App\Http\Controllers\Admin;

use App\Models\Estoque;
use App\Models\Entrada;
use App\Models\Saida;
use App\Http\Controllers\Admin\EstoqueCrudController;
use App\Http\Controllers\Admin\EntradaCrudController;
use App\Http\Requests\SaidaRequest;
use Illuminate\Support\Facades\DB;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Services\RedirectorService;

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

        $produtos = Estoque::with('produto.ufs')->get();
        $Itens = $produtos->map(function ($produto) {
            return ['id' => $produto->produto_id, 'name' => $produto->produto->nome.' - '.$produto->produto->ufs->uf];          
        })->pluck('name', 'id')->toArray();

        asort($Itens);
        CRUD::field([   // select_from_array
            'name'        => 'produto_id',//Aqui ele pega o id da entrada
            'label'       => "Produto",
            'type'        => 'select_from_array',
            'options'     => [null => 'Escolha um produto'] +$Itens,
            'allows_null' => false,
            'default'     => 'one',
        ]);
        CRUD::field([   
            'name'        => 'validades',
            'label'       => 'Validade',
            'type'        => 'select_from_array',
            'options'     => [null => 'Escolha uma data de validade'],
            'allows_null' => false,
            'default'     => 'one',
            'attributes' => [
                'disabled'    => 'disabled',
            ]
        ]);
        CRUD::field([  
            'label'     => "Quantidade Disponivel",
            'type'      => 'text',
            'name'      => 'quantidadedisponivel',
            'attributes' => [
                'disabled'    => 'disabled',
            ]
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
        CRUD::field([
            'name'  => 'entrada_id',//Aqui ele pega o id da entrada
            'type'  => 'hidden',
        ]);
    }

    public function store()
    {
        DB::beginTransaction();// Inicia a transação do banco de dados
        try {
            $request = $this->crud->validateRequest();
            
            $estoque = Estoque::where('produto_id', $request->produto_id)->first();
            
            $entrada = Entrada::where('id', $request->entrada_id)->first();
            
            $estoqueController = new EstoqueCrudController();
            $estoqueController->removeQuantidade($request, $estoque);

            $quantidade = $entrada->atualizarQuantidadeSaidaNaEntrada($request->input('quantidade'));
            
            EntradaCrudController::updateQuantidade($entrada,$quantidade['quantidade']);
            
            if($quantidade['quantidadeSaida'] == 0){
                $estoqueController->removeValidade($estoque,$entrada->validade);
            }

            $request['estoque_id'] = $estoque->id;

            $entry = $this->crud->create($request->except(['_token', '_method'])); 

            DB::commit();// Se tudo correu bem, commit na transação
            $rota = RedirectorService::redirecionamentoRotas($request->get('_save_action'), $entry, 'saida');
            return $rota;
        } catch (\Exception $e) {
            DB::rollback();// Se ocorrer uma exceção, reverta a transação
            throw $e;
        }
    }

    protected function setupUpdateOperation()
    {
        CRUD::setValidation(SaidaRequest::class);
        $saida = Saida::with('estoque.produto.ufs')->findOrFail($this->crud->getCurrentEntry()->id);

        CRUD::field([   // select_from_array
            'name'        => 'estoque_id_disable',//Aqui ele pega o id da entrada
            'label'       => "Produto",
            'type'        => 'text',
            'value'       => $saida->estoque->produto->nome.' - '.$saida->estoque->produto->ufs->uf.' - '.date('d/m/Y', strtotime($saida->entrada->validade)),
            'attributes' => [
                'disabled'    => 'disabled',
            ]
        ]);  
        CRUD::field([   
            'name'        => 'validades',
            'label'       => 'Validade',
            'type'        => 'text',
            'value'       => date('d/m/Y', strtotime($saida->entrada->validade)),
            'attributes' => [
                'disabled'    => 'disabled',
            ]
        ]);
        CRUD::field([  
            'label'     => "Quantidade Disponivel",
            'type'      => 'text',
            'name'      => 'quantidadedisponivel',
            'value'     => (($saida->entrada->quantidade - $saida->entrada->qtdSaidas) == 0) ? $saida->quantidade : ($saida->entrada->quantidade - $saida->entrada->qtdSaidas),
            'attributes' => [
                'disabled'    => 'disabled',
            ]
        ]);
        CRUD::field([  
            'label'     => "Quantidade",
            'type'      => 'text',
            'name'      => 'quantidade',
        ]);  
        CRUD::field([
            'name'  => 'entrada_id',//Aqui ele pega o id da entrada
            'type'  => 'hidden',
            'value' => $saida->entrada_id,
        ]);
        CRUD::field([
            'name'  => 'produto_id',//Aqui ele pega o id da entrada
            'type'  => 'hidden',
            'value' => $saida->estoque->produto_id,
        ]);


    }
    public function update()
    {
        DB::beginTransaction();// Inicia a transação do banco de dados
        try {
            $request = $this->crud->validateRequest();
            $saida = Saida::find($request->id);
            $entrada = Entrada::find($saida->entrada_id);
            $estoque = Estoque::find($saida->estoque_id);

            $quantidadeNova = $estoque->atualizarQuantidadeSaida($request->quantidade, $saida->quantidade);

            $estoqueController = new EstoqueCrudController();
            if($quantidadeNova['total'] == 0 || ($quantidadeNova['total'] == $entrada->quantidade)){
                $estoqueController->removeValidade($estoque,$entrada->validade);
            }else{
                $estoqueController->voltaValidade($estoque, $entrada->validade);
            }

            $quantidadeNovaEntrada = $entrada->atualizarQuantidadeSaidaNaSaida($request->quantidade, $saida->quantidade);

            $saida->update(['quantidade' => intval($request->quantidade)]);
            $entrada->update(['qtdSaidas' =>  $quantidadeNovaEntrada['qtdNova']]);
            $estoque->update(['qtdTotal' => $quantidadeNova['qtdNova']]);

            DB::commit();// Se tudo correu bem, commit na transação
            $rota = RedirectorService::redirecionamentoRotas($request->get('_save_action'), $request, 'saida');
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
                return $entry->estoque->produto->nome; 
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
                return $entry->estoque->produto->ufs->uf;
            },
            'searchLogic'    => true,
            'orderable'      => true,
            'visibleInModal' => true,
        ]);
        CRUD::addColumn([
            'name' => 'validade',
            'label' => 'Validade',
            'type' => 'text',
            'value' => function($entry) {
                return date('d/m/Y', strtotime($entry->entrada->validade));
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

            $this->devolverValidadeEQuantidadeParaEstoque($entrada, $estoque,$saida);

            //Devolução de Quantidade saida para entrada;
            $quantidadeNova = $entrada->removeQuantidadeEntrada($saida->quantidade);
            $this->atualizarQuantidadeEntrada($entrada, $quantidadeNova);

            $saida->delete();
            DB::commit();// Se tudo correu bem, commit na transação
            \Alert::success("Saida cancelada com sucesso")->flash();
            return redirect("/admin/saida");
        } catch (\Exception $e) {
            DB::rollback();// Se ocorrer uma exceção, reverta a transação
            throw $e;
        }
    }

    private function devolverValidadeEQuantidadeParaEstoque($entrada, $estoque, $saida)
    {
        $estoqueController = new EstoqueCrudController();
        $estoqueController->voltaValidade($estoque, $entrada->validade);

        $quantidade = $saida->devolveQuantidadeSaida($estoque->qtdTotal);
        $estoqueController->updateQuantidade($estoque, $quantidade);
    }

    private function atualizarQuantidadeEntrada($entrada, $quantidadeNova)
    {
        EntradaCrudController::updateQuantidade($entrada, $quantidadeNova);
    }
}

