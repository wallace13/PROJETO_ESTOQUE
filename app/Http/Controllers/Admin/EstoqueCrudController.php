<?php

namespace App\Http\Controllers\Admin;

use App\Models\Movimentacao;
use App\Http\Requests\EstoqueRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class EstoqueCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class EstoqueCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Estoque::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/estoque');
        CRUD::setEntityNameStrings('Estoque', 'estoque');

        $this->crud->denyAccess('create');
        $this->crud->denyAccess('update');
        $this->crud->denyAccess('delete');
    }

    protected function setupListOperation()
    {
        $this->setupCommonColumns();
    }
    /*
    protected function setupCreateOperation()
    {
        CRUD::setValidation(EstoqueRequest::class);
        CRUD::setFromDb(); 
    }
    
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
    */
    protected function setupShowOperation()
    {
        $this->setupCommonColumns();
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
            'name' => 'produto_id',
            'label' => 'Produto',
            'type' => 'text', 
            'value' => function($entry) {
                $produto = \App\Models\Produto::find($entry->produto_id);
                if ($produto) {
                    return $produto->nome; 
                }
                return 'Produto não encontrado';
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
                $ufId = \App\Models\Produto::find($entry->produto_id);
                if ($ufId->uf_id) {
                    $uf = \App\Models\Uf::find($ufId->uf_id); 
                    return $uf->uf;
                }
                return 'Uf não encontrado';
            },
            'searchLogic'    => true,
            'orderable'      => true,
            'visibleInModal' => true,
        ]);
        CRUD::addColumn([
            'name' => 'qtdTotal',
            'label' => 'Quantidade',
            'type' => 'text',
            'searchLogic'    => true,
            'orderable'      => true,
            'visibleInModal' => true,
        ]);
    }
}
