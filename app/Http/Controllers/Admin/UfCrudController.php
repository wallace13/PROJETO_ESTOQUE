<?php

namespace App\Http\Controllers\Admin;

use App\Models\Uf;
use App\Models\Produto;
use App\Http\Requests\UfRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class UfCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UfCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    //use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Uf::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/uf');
        CRUD::setEntityNameStrings('Unidade de Fornecimento', 'Unidade de Fornecimento');
    }

    protected function setupListOperation()
    {
        $this->setupCommonColumns();
        $this->crud->addButtonFromView('line', 'remover', 'remover', 'end');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(UfRequest::class);
        CRUD::setFromDb(); 
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
                $uf = Uf::with('users')->findOrFail($entry->id);
                if ($uf) {
                    return $uf->users->name; 
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
            'name' => 'descricao',
            'label' => 'Unidade de Fornecimento',
            'type' => 'text', 
        ]);
        CRUD::addColumn([
            'name' => 'uf',
            'label' => 'Sigla',
            'type' => 'text', 
        ]);
    }
    public function remover($id){
        $produto = Produto::where("uf_id", $id)->first();
        if ($produto) {
            \Alert::error("Não é possivel excluir, pois uf pertence à um produto")->flash();
            return redirect("/admin/uf");
        } else {
            try {
                $uf = Uf::find($id);
                $uf->delete();
                \Alert::success("Uf excluído com sucesso")->flash();
                return redirect("/admin/uf");
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }
}