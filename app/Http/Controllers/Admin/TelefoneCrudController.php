<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TelefoneRequest;
use App\Models\Telefone;
use Illuminate\Support\Facades\Auth;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class TelefoneCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TelefoneCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    //use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
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
        CRUD::setModel(\App\Models\Telefone::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/telefone');
        CRUD::setEntityNameStrings('telefone', 'telefones');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        //CRUD::setFromDb(); 
        CRUD::addColumn([
            'name' => 'fornecedor_id',
            'label' => 'Fornecedor',
            'type' => 'text', 
            'value' => function($entry) {
                return $entry->fornecedores->razao_social;
            },
        ]);
        CRUD::addColumn([
            'name' => 'responsavel',
            'label' => 'Responsável',
            'type' => 'text', 
            'value' => function($entry) {
                return $entry->fornecedores->responsavel_legal;
            },
        ]);
        CRUD::addColumn([
            'name' => 'tipo_telefone',
            'label' => 'Tipo',
            'type' => 'text', 
            'value' => function($entry) {
                $resultado = $entry->getTipoTelefoneFormattedAttribute();
                return $resultado;
            },
        ]);
        CRUD::addColumn([
            'name' => 'numero_telefone',
            'label' => 'Número',
            'type' => 'text', 
            'value' => function($entry) {
                $resultado = $entry->getNumeroFormattedAttribute();
                return $resultado;
            },
        ]);
    }
    protected function setupShowOperation()
    {
        $this->setupListOperation();
        CRUD::addColumn([
            'name' => 'user_id',
            'label' => 'Criado por',
            'type' => 'text', 
            'value' => function ($entry) {
                $user = Telefone::with('users')->findOrFail($entry->id);
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

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(TelefoneRequest::class);
        CRUD::addField([   
            'name'        => 'user_id',
            'label'       => 'Id Usuario',
            'type' => 'hidden',
        ]);
        CRUD::addField([   
            'name'        => 'fornecedor_id',
            'label'       => 'Id Fornecedor',
            'type' => 'hidden',
        ]);
        CRUD::addField([   
            'name'        => 'tipo_telefone',
            'label'       => 'Tipo de Telefone',
            'type' => 'select_from_array',
            'default'     => '',
            'options' => [
                ''  => 'Selecione o tipo',
                '0' => 'Telefone',
                '1' => 'Comercial',
                '2' => 'Celular',
            ],
        ]);
        CRUD::addField([   
            'name'        => 'ddd',
            'label'       => 'DDD',
            'type' => 'text',
        ]);
        CRUD::addField([   
            'name'        => 'numero_telefone',
            'label'       => 'Número Telefone',
            'type' => 'text',
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
    public function store($tipo, $ddd, $numero, $idFornecedor)
    {
        try {
            $user_id = backpack_auth()->user()->id;

            Telefone::create([
                'tipo_telefone' => $tipo,
                'ddd' => $ddd, 
                'numero_telefone' => $numero,
                'fornecedor_id' => $idFornecedor,
                'user_id'          => $user_id, 
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function updateTelefoneFornecedor($tipo,$ddd,$numero,$idFornecedor,$idTelefone)
    {
        try {
            $telefone = Telefone::find($idTelefone);

            $telefone->update([
                'tipo_telefone' => $tipo,
                'ddd' => $ddd, 
                'numero_telefone' => $numero,
                'fornecedor_id' => $idFornecedor
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
