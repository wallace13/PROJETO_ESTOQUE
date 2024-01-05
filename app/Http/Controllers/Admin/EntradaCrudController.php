<?php

namespace App\Http\Controllers\Admin;

use App\Models\Estoque;
use App\Models\Entrada;
use App\Models\Produto;
use App\Http\Controllers\Admin\EstoqueCrudController;
use App\Http\Requests\EntradaRequest;
use Illuminate\Support\Facades\DB;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Services\RedirectorService;

class EntradaCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    //use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
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
        $this->crud->addButtonFromView('line', 'cancelarEntrada', 'cancelarEntrada', 'end');
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

            $estoqueController = new EstoqueCrudController();
            $request['estoque_id'] = $estoqueController->store($request);

            $entry = $this->crud->create($request->except(['_token', '_method']));

            DB::commit();// Se tudo correu bem, commit na transação
            $rota = RedirectorService::redirecionamentoRotas($request->get('_save_action'), $entry, 'entrada');
            return $rota;
        } catch (\Exception $e) {
            DB::rollback();// Se ocorrer uma exceção, reverta a transação
            throw $e;
        }
    }
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();

        $estoques = Estoque::orderBy('id')->get();
        $Itens = $estoques->map(function ($estoque) {
            return ['id' => $estoque->id, 'name' => $estoque->produto->formatted_name];
        })->pluck('name', 'id')->toArray();
        asort($Itens);
        
        CRUD::field([   // select_from_array
            'name'        => 'produto_id',
            'label'       => "Produto",
            'type'        => 'select_from_array',
            'value'       => $this->crud->getCurrentEntry()->estoque_id, 
            'options'     => $Itens,
            'allows_null' => false,
            'default'     => 'one',
            'attributes' => [
                'disabled'    => 'disabled',
            ],
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
            
            $estoqueController = new EstoqueCrudController();
            $estoque = $estoqueController->update($entrada, $request);
            $request['estoque_id'] = $estoque;

            $entrada->update($request->all());

            DB::commit();// Se tudo correu bem, commit na transação
            $rota = RedirectorService::redirecionamentoRotas($request->get('_save_action'), $request, 'entrada');
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
    public static function updateQuantidade($entrada, $quantidade){
        $entrada->update(['qtdSaidas' => $quantidade]);
    }

    public function cancelarEntrada($id){
        DB::beginTransaction();
        try {
            $entrada = Entrada::find($id);
            $estoque = Estoque::find($entrada->estoque_id);
            if($entrada->qtdSaidas == 0){

                $this->removeValidadeEQuantidadeParaEstoque($entrada, $estoque);
                $entrada->delete();

                DB::commit();// Se tudo correu bem, commit na transação
                \Alert::success("Entrada cancelada com sucesso")->flash();
                return redirect("/admin/entrada");
            }else{
                \Alert::error("Não é possivel cancelar, pois entrada já possui saídas")->flash();
                return redirect("/admin/entrada");
            }
        } catch (\Exception $e) {
            DB::rollback();// Se ocorrer uma exceção, reverta a transação
            throw $e;
        }
    }
    private function removeValidadeEQuantidadeParaEstoque($entrada, $estoque)
    {
        $estoqueController = new EstoqueCrudController();
        $quantidade = $entrada->countValidadeEntrada($entrada->validade, $entrada->estoque_id);
        
        if($quantidade <= 1){
            $estoqueController->removeValidade($estoque, $entrada->validade); 
        }

        $quantidade = $entrada->removeQuantidadeEntradaEstoque($estoque->qtdTotal);
        $estoqueController->updateQuantidade($estoque, $quantidade);
    }
}
