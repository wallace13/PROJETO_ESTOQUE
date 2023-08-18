<?php

namespace App\Http\Controllers\Admin;

use App\Models\Produto;
use App\Models\Uf;
use App\Http\Requests\ProdutoRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ProdutoCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProdutoCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Produto::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/produto');
        CRUD::setEntityNameStrings('produto', 'produtos');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->setupCommonColumns();
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ProdutoRequest::class);
        $ufs = Uf::All()->pluck('uf', 'id')->toArray();
        asort($ufs);
        
        CRUD::field('nome')->type('text')->label('Nome');
        CRUD::field([   // select_from_array
            'name'        => 'uf_id',
            'label'       => "Unidade de Fornecimento (UF)",
            'type'        => 'select_from_array',
            'options'     => [null => 'Escolha um produto'] +$ufs,
            'allows_null' => false,
            'default'     => 'one',
        ]);
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
    protected function setupShowOperation()
    {
        //CRUD::setFromDb(); 
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
            'name' => 'nome',
            'label' => 'Produto',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'name' => 'uf_id',
            'label' => 'UF',
            'type' => 'text', 
            'value' => function($entry) {
                $uf = \App\Models\Uf::find($entry->uf_id);
                if ($uf) {
                    return $uf->uf; 
                }
                return 'Uf não encontrado';
            },
        ]);
    }
}
