<?php

namespace App\Http\Controllers\Admin;

use App\Models\Produto;
use App\Models\Uf;
use App\Models\Categoria;
use App\Models\Estoque;
use App\Http\Requests\ProdutoRequest;
use App\Models\Fornecedor;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ProdutoCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    //use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Produto::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/produto');
        CRUD::setEntityNameStrings('produto', 'produtos');
    }

    protected function setupListOperation()
    {
        $this->setupCommonColumns();
        $this->crud->addButtonFromView('line', 'remover', 'remover', 'end');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(ProdutoRequest::class);
        $ufs = Uf::All()->pluck('uf', 'id')->toArray();
        asort($ufs);
        $categorias = Categoria::All()->pluck('descricao', 'id')->toArray();
        asort($categorias);

        $fornecedores = Fornecedor::orderBy('id')->get();
        $dadosFornecedor = $fornecedores->map(function ($fornecedor) {
            return ['id' => $fornecedor->id, 'name' => $fornecedor->formatted_name];
        })->pluck('name', 'id')->toArray();
        asort($dadosFornecedor);

        CRUD::field('nome')->type('text')->label('Nome');
        CRUD::field([   // select_from_array
            'name'        => 'uf_id',
            'label'       => "Unidade de Fornecimento (UF)",
            'type'        => 'select_from_array',
            'options'     => [null => 'Escolha um produto'] +$ufs,
            'allows_null' => false,
            'default'     => 'one',
        ]);
        CRUD::field([   // select_from_array
            'name'        => 'categoria_id',
            'label'       => "Categoria",
            'type'        => 'select_from_array',
            'options'     => [null => 'Escolha uma categoria'] +$categorias,
            'allows_null' => false,
            'default'     => 'one',
        ]);
        CRUD::field([   // select_from_array
            'name'        => 'fornecedor_id',
            'label'       => "Fornecedor",
            'type'        => 'select_from_array',
            'options'     => [null => 'Escolha uma fornecedor'] +$dadosFornecedor,
            'allows_null' => false,
            'default'     => 'one',
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

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
    protected function setupShowOperation()
    {
        $this->setupCommonColumns();
        CRUD::addColumn([
            'name' => 'user_id',
            'label' => 'Criado por',
            'type' => 'text', 
            'value' => function ($entry) {
                $user = Produto::with('users')->findOrFail($entry->id);
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
            'name' => 'nome',
            'label' => 'Produto',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'name' => 'uf_id',
            'label' => 'UF',
            'type' => 'text', 
            'value' => function($entry) {
                $uf = Uf::find($entry->uf_id);
                if ($uf) {
                    return $uf->uf; 
                }
                return 'Uf não encontrado';
            },
        ]);
        CRUD::addColumn([
            'name' => 'categoria_id',
            'label' => 'Categoria',
            'type' => 'text', 
            'value' => function($entry) {
                $categoria = Categoria::find($entry->categoria_id);
                if ($categoria) {
                    return $categoria->descricao; 
                }
                return 'Uf não encontrado';
            },
        ]);
        CRUD::addColumn([
            'name' => 'fornecedor_id',
            'label' => 'Fornecido por',
            'type' => 'text', 
            'value' => function($entry) {
                $fornecedor =   Fornecedor::find($entry->fornecedor_id);
                if ($fornecedor) {
                    return $fornecedor->razao_social; 
                }
                return 'Fornecedor não encontrado';
            },
        ]);
    }
    public function remover($id){
        $estoque = Estoque::where("produto_id", $id)->first();
       
        if ($estoque) {
            \Alert::error("Não é possivel excluir, pois produto já foi para o estoque")->flash();
            return redirect("/admin/produto");
        } else {
            try {
                $produto = Produto::find($id);
                $produto->delete();
                \Alert::success("Produto excluído com sucesso")->flash();
                return redirect("/admin/produto");
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }
}