<?php

namespace App\Http\Controllers\Admin;

use App\Models\Estoque;
use App\Models\Entrada;
use App\Models\Produto;
use App\Models\Uf;
use App\Http\Requests\EntradaRequest;
use Illuminate\Support\Facades\DB;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class EntradaCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Entrada::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/entrada');
        CRUD::setEntityNameStrings('Entrada', 'Entradas');
    }

    protected function setupListOperation()
    {
        $this->setupCommonColumns();
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(EntradaRequest::class);
        $produtos = Produto::orderBy('id')->get();
        $Itens = $produtos->map(function ($produto) {
            return ['id' => $produto->id, 'name' => $produto->formatted_name];
        })->pluck('name', 'id')->toArray();
        asort($Itens);
        CRUD::field([   // select_from_array
            'name'        => 'produto_id',
            'label'       => "Produto",
            'type'        => 'select_from_array',
            'options'     => [null => 'Escolha um produto'] +$Itens,
            'allows_null' => false,
            'default'     => 'one',
        ]);
        CRUD::field([  
            'label'     => "Validade",
            'type'      => 'date',
            'name'      => 'validade',
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

            $request['estoque_id'] = $this->storeEstoque($request);
            $entry = $this->crud->create($request->except(['_token', '_method']));

            DB::commit();// Se tudo correu bem, commit na transação
            // Use o método causedBy para definir o "Causer" como o usuário autenticado.
            $rota = $this->redirecionamentoRotas($request->get('_save_action'), $entry);
            return $rota;
        } catch (\Exception $e) {
            DB::rollback();// Se ocorrer uma exceção, reverta a transação
            throw $e;
        }
    }
    
    public function storeEstoque($request)
    {
        $estoque = Estoque::firstOrNew(['produto_id' => $request->produto_id]);

        $estoque->qtdTotal = ($estoque->exists) ?  $estoque->qtdTotal += $request->quantidade : $request->quantidade;
    
        $validades = $estoque->decodeValidadesJSON($estoque->validades);
        if($validades === null){
            $validades[] = $estoque->criaArrayValidades($request->validade);
        }else{
            $indiceItem = $estoque->buscaValidadeNoArray($request->validade, $validades);
        
            if ($indiceItem === false) {
                $validades[] = $estoque->criaArrayValidades($request->validade);
            }
        }

        $estoque->validades = $estoque->encodeValidadesJSON($validades);
        $estoque->save();
        return $estoque->id;
    }

    public function redirecionamentoRotas($saveAction,$entry){
        if ($saveAction === 'save_and_back') {
            return redirect("/admin/entrada");
        } elseif ($saveAction === 'save_and_edit') {
            return redirect("/admin/entrada/{$entry->id}/edit");
        } elseif ($saveAction === 'save_and_preview') {
            return redirect("/admin/entrada/{$entry->id}/show");
        }elseif ($saveAction === 'save_and_new') {
            return redirect("/admin/entrada/create");
        }
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
        $estoqueSelecionado = Estoque::find($this->crud->getCurrentEntry()->estoque_id);
        $produtos = Produto::orderBy('id')->get();
        $Itens = $produtos->map(function ($produto) {
            return ['id' => $produto->id, 'name' => $produto->formatted_name];
        })->pluck('name', 'id')->toArray();
        asort($Itens);
        CRUD::field([   // select_from_array
            'name'        => 'produto_id',
            'label'       => "Produto",
            'type'        => 'select_from_array',
            'value'       => $estoqueSelecionado->produto_id, 
            'options'     => $Itens,
            'allows_null' => false,
            'default'     => 'one',
        ]);
        CRUD::field([  
            'label'     => "Quantidade de saidas",
            'type'      => 'hidden',
            'name'      => 'qtdSaidas',
        ]);  
    }
    public function update()
    {
        DB::beginTransaction();// Inicia a transação do banco de dados
        try {
            $request = $this->crud->validateRequest();
            $entrada = Entrada::find($request->id);

            $estoque = $this->updateEstoque($entrada, $request);

            $entrada->update(
                ['quantidade' => $request->quantidade, 
                'validade' => $request->validade, 
                'estoque_id' => $estoque]);

            DB::commit();// Se tudo correu bem, commit na transação
            $rota = $this->redirecionamentoRotas($request->get('_save_action'), $request);
            return $rota;
        } catch (\Exception $e) {
            DB::rollback();// Se ocorrer uma exceção, reverta a transação
            throw $e;
        }
        
    }
    public function updateEstoque($entrada, $request)
    {
        $estoque = Estoque::find($entrada->estoque_id);

        $quantidadeNova = $estoque->atualizarQuantidadeEntrada($request->quantidade, $entrada->quantidade);

        $validades = $estoque->decodeValidadesJSON($estoque->validades);

        $indiceItem = $estoque->buscaValidadeNoArray($entrada->validade, $validades);
        $qtdItem = $estoque->countValidadeEntrada($entrada);

        if ($indiceItem !== false && $qtdItem <= 1) {
            $validades = $estoque->removeValidade($indiceItem, $validades);
        }
        $indice = $estoque->buscaValidadeNoArray($request->validade, $validades);

        if ($indice === false) {
            $validades[] = $estoque->criaArrayValidades($request->validade);
        }
        
        $estoque->update([
        'qtdTotal' => $quantidadeNova,'validades' => json_encode($validades),'produto_id' => $request->produto_id]);
        
        return $estoque->id;
    }
    protected function setupShowOperation()
    {
        $this->setupCommonColumns();
        CRUD::addColumn([
            'name' => 'user_id',
            'label' => 'Criado por',
            'type' => 'text', 
            'value' => function ($entry) {
                $user = Entrada::with('users')->findOrFail($entry->id);
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
                $entrada = Entrada::with('estoque.produto.ufs')->findOrFail($entry->id);
                if ($entrada) {
                    return $entrada->estoque->produto->nome;
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
                $entrada = Entrada::with('estoque.produto.ufs')->findOrFail($entry->id);
                if ($entrada) {
                    return $entrada->estoque->produto->ufs->uf; 
                }
                return 'Uf do Produto não encontrada';
            },
            'searchLogic'    => true,
            'orderable'      => true,
            'visibleInModal' => true,
        ]);
        CRUD::addColumn([
            'name' => 'validade',
            'label' => 'Validade',
            'type' => 'text', // ou 'date' se quiser manter o formato da data
            'value' => function ($entry) {
                return date('d/m/Y', strtotime($entry->validade));
            },
        ]);
        CRUD::addColumn([
            'name' => 'quantidade',
            'label' => 'Quantidade',
            'type' => 'text',
            'searchLogic'    => true,
            'orderable'      => true,
            'visibleInModal' => true,
        ]);
        CRUD::addColumn([
            'name' => 'qtdSaidas',
            'label' => 'Quantidade Saidas',
            'type' => 'text',
            'searchLogic'    => true,
            'orderable'      => true,
            'visibleInModal' => true,
        ]);
    }
}
