<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\FornecedorRequest;
use App\Models\Fornecedor;
use App\Http\Controllers\Admin\TelefoneCrudController;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;
use App\Services\RedirectorService;

/**
 * Class FornecedorCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class FornecedorCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Fornecedor::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/fornecedor');
        CRUD::setEntityNameStrings('fornecedor', 'fornecedores');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        //CRUD::setFromDb(); // set columns from db columns.
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
        CRUD::setValidation(FornecedorRequest::class);
        //CRUD::setFromDb(); // set fields from db columns.
        CRUD::addField([   
            'name'        => 'razao_social',
            'label'       => 'Razão Social',
            'type'        => 'text',
            'attributes'  => [
                'placeholder' => 'Digite a Razão Social',
            ],
            'tab' => 'Fornecedor',
        ]);
        CRUD::addField([
            'name' => 'cnpj',
            'label' => 'CNPJ',
            'type' => 'text',
            'tab' => 'Fornecedor',
            'attributes' => [
                'placeholder' => 'Digite o CNPJ',
            ],
        ]);
        CRUD::addField([   
            'name'        => 'nome_fantasia',
            'label'       => 'Nome Fantasia',
            'type'        => 'text',
            'attributes'  => [
                'placeholder' => 'Digite o Nome Fantasia',
            ],
            'tab' => 'Fornecedor',
        ]);
        CRUD::addField([   
            'name'        => 'inscricao_estadual',
            'label'       => 'Inscrição Estadual',
            'type'        => 'text',
            'attributes'  => [
                'placeholder' => 'Digite o número da inscrição',
            ],
            'tab' => 'Fornecedor',
        ]);
        CRUD::addField([   
            'name'        => 'inscricao_municipal',
            'label'       => 'Inscrição Municipal',
            'type'        => 'text',
            'attributes'  => [
                'placeholder' => 'Digite o número da inscrição',
            ],
            'tab' => 'Fornecedor',
        ]);
        CRUD::addField([   
            'name'        => 'data_fundacao',
            'label'       => 'Data da fundação',
            'type'        => 'date',
            'attributes'  => [
                'placeholder' => 'Digite o data',
            ],
            'tab' => 'Fornecedor',
        ]);
        CRUD::addField([   
            'name'        => 'situacao_cadastral',
            'label'       => 'Situação Cadastral',
            'type' => 'select_from_array',
            'options' => [
                'ativa' => 'Ativa',
                'inativa' => 'Inativa',
            ],
            'tab' => 'Fornecedor',
        ]);
        CRUD::addField([   
            'name'        => 'cep',
            'label'       => 'CEP',
            'type'        => 'text',
            'attributes'  => [
                'placeholder' => 'Digite o cep',
            ],
            'tab' => 'Endereço',
        ]);
        CRUD::addField([   
            'name'        => 'logradouro',
            'label'       => 'Logradouro',
            'type'        => 'text',
            'attributes'  => [
                'placeholder' => 'Digite o logradouro',
            ],
            'tab' => 'Endereço',
        ]);
        CRUD::addField([   
            'name'        => 'complemento',
            'label'       => 'Complemento',
            'type'        => 'text',
            'attributes'  => [
                'placeholder' => 'Digite o complemento',
            ],
            'tab' => 'Endereço',
        ]);
        CRUD::addField([   
            'name'        => 'numero',
            'label'       => 'Número',
            'type'        => 'text',
            'attributes'  => [
                'placeholder' => 'Digite o número',
            ],
            'tab' => 'Endereço',
        ]);
        CRUD::addField([   
            'name'        => 'bairro',
            'label'       => 'Bairro',
            'type'        => 'text',
            'attributes'  => [
                'placeholder' => 'Digite o bairro',
            ],
            'tab' => 'Endereço',
        ]);
        CRUD::addField([   
            'name'        => 'cidade',
            'label'       => 'Cidade',
            'type'        => 'text',
            'attributes'  => [
                'placeholder' => 'Digite o cidade',
            ],
            'tab' => 'Endereço',
        ]);
        CRUD::addField([   
            'name'        => 'estado',
            'label'       => 'Estado',
            'type'        => 'text',
            'attributes'  => [
                'placeholder' => 'Digite o estado',
            ],
            'tab' => 'Endereço',
        ]);
        CRUD::addField([   
            'name'        => 'responsavel_legal',
            'label'       => 'Responsável Legal',
            'type'        => 'text',
            'attributes'  => [
                'placeholder' => 'Digite o nome',
            ],
            'tab' => 'Contato',
        ]);
        for ($i=0; $i < 3; $i++) { 
            CRUD::addField([   
                'name'        => "tipo_telefone_{$i}",
                'label'       => ($i > 0) ? 'Tipo de Telefone (Opcional)' : 'Tipo de Telefone',
                'type' => 'select_from_array',
                'options' => [
                    '0' => 'Telefone',
                    '1' => 'Comercial',
                    '2' => 'Celular',
                ],
                'tab' => 'Contato',
                'wrapper'     => [
                    'class' => 'form-group col-md-4', // Defina a largura desejada
                ],
            ]);
            CRUD::addField([
                'name'       => "ddd_{$i}",
                'label'      => ($i > 0) ? 'DDD (Opcional)' : 'DDD',
                'type'       => 'text',
                'tab'        => 'Contato',
                'wrapper'     => [
                    'class' => 'form-group col-md-2', // Defina a largura desejada
                ],
                'attributes' => [
                    'maxlength' => 2, // Adiciona a regra de tamanho máximo
                ],
            ]);
            CRUD::addField([
                'name'       => "numero_telefone_{$i}",
                'label'      => ($i > 0) ? 'Número (Opcional)' : 'Número',
                'type'       => 'text',
                'tab'        => 'Contato',
                'wrapper'     => [
                    'class' => 'form-group col-md-6', // Defina a largura desejada
                ],
            ]);
        }
        CRUD::addField([   
            'name'        => 'email',
            'label'       => 'E-mail',
            'type'        => 'email',
            'attributes'  => [
                'placeholder' => 'Digite o e-mail',
            ],
            'tab' => 'Contato',
        ]);
        CRUD::addField([   
            'name'        => 'email_opcional',
            'label'       => 'E-mail (Opcional)',
            'type'        => 'email',
            'attributes'  => [
                'placeholder' => 'Digite o e-mail',
            ],
            'tab' => 'Contato',
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
    protected function setupCommonColumns()
    {
        CRUD::addColumn([
            'name' => 'razao_social',
            'label' => 'Razão Social',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'name' => 'nome_fantasia',
            'label' => 'Nome Fantasia',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'name' => 'cnpj',
            'label' => 'CNPJ',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'name' => 'telefone',
            'label' => 'Telefone',
            'type' => 'text',
            'value' => function($entry) {
                return "({$entry->telefones[0]->ddd}) ".$entry->telefones[0]->numero_telefone; 
            },
        ]);
        CRUD::addColumn([
            'name' => 'telefone1',
            'label' => 'Telefone',
            'type' => 'text',
            'value' => function($entry) {
                $resultado = (isset($entry->telefones[1]->ddd)) ? "({$entry->telefones[1]->ddd}) ".$entry->telefones[1]->numero_telefone : "Não Informado" ;
                return $resultado; 
            },
        ]);
        CRUD::addColumn([
            'name' => 'telefone2',
            'label' => 'Telefone',
            'type' => 'text',
            'value' => function($entry) {
                $resultado = (isset($entry->telefones[2]->ddd)) ? "({$entry->telefones[2]->ddd}) ".$entry->telefones[2]->numero_telefone : "Não Informado" ;
                return $resultado; 
            },
        ]);
        CRUD::addColumn([
            'name' => 'email',
            'label' => 'E-mail',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'name' => 'responsavel_legal',
            'label' => 'Responsável Legal',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'name' => 'inscricao_estadual',
            'label' => 'Inscrição Estadual',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'name' => 'inscricao_municipal',
            'label' => 'Inscrição Municipal',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'name' => 'data_fundacao',
            'label' => 'Fundada em',
            'type' => 'text',
            'value' => function ($entry) {
                return date('d/m/Y', strtotime($entry->data_fundacao));
            },
        ]);
        CRUD::addColumn([
            'name' => 'situacao_cadastral',
            'label' => 'Situação',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'name' => 'logradouro',
            'label' => 'Logradouro',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'name' => 'complemento',
            'label' => 'Complemento',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'name' => 'numero',
            'label' => 'Número',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'name' => 'cep',
            'label' => 'CEP',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'name' => 'bairro',
            'label' => 'Bairro',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'name' => 'cidade',
            'label' => 'Cidade',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'name' => 'estado',
            'label' => 'Estado',
            'type' => 'text'
        ]);
    }
    protected function setupShowOperation()
    {
        $this->setupCommonColumns();
        CRUD::addColumn([
            'name' => 'user_id',
            'label' => 'Criado por',
            'type' => 'text', 
            'value' => function ($entry) {
                $user = Fornecedor::with('users')->findOrFail($entry->id);
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
    public function store()
    {
        DB::beginTransaction();// Inicia a transação do banco de dados
        try {
            $request = $this->crud->validateRequest();

            $entry = $this->crud->create($request->except(['_token', '_method']));
            $i = 0;
            while ($i < 3) {
                $tipo = $request->input("tipo_telefone_{$i}");
                $ddd = $request->input("ddd_{$i}");
                $numero = $request->input("numero_telefone_{$i}");
                if (isset($tipo) && isset($ddd) && isset($numero)) {
                    $telefoneController = new TelefoneCrudController();
                    $telefoneController->store($tipo,$ddd,$numero,$entry->id);
                }
                $i++;
            }

            DB::commit();// Se tudo correu bem, commit na transação
            $rota = RedirectorService::redirecionamentoRotas($request->get('_save_action'), $entry, 'fornecedor');
            return $rota;
        } catch (\Exception $e) {
            DB::rollback();// Se ocorrer uma exceção, reverta a transação
            throw $e;
        }
    }
}
