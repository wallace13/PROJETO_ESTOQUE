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
    }
    
    public function store(){
        $request = $this->crud->validateRequest();

        $estoque = Estoque::firstOrNew(['produto_id' => $request->produto_id]);

        if ($estoque->exists) {
            // Obtenha o JSON atual e converta em array
            $validades = json_decode($estoque->validades, true);

            $estoque->qtdTotal += $request->quantidade;
            $novaValidade = $request->validade;
            $validades[] = $novaValidade;

            // Converta o array de volta para JSON
            $estoque->validades = json_encode($validades);
        } else {
            $estoque->qtdTotal = $request->quantidade;

            // Inicialize o campo 'validades' com um array contendo a nova validade
            $estoque->validades = json_encode([$request->validade]);
        }

        $estoque->save();
        $request['estoque_id'] = $estoque->id;
        $entry = $this->crud->create($request->except(['_token', '_method']));
        return redirect("/admin/entrada");
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
    }
    public function update()
    {
        $request = $this->crud->validateRequest();
        $entrada = Entrada::find($request->id);
        $estoque = Estoque::find($entrada->estoque_id);

        if (intval($request->quantidade) > $entrada->quantidade) {
            $total = intval($request->quantidade) - $entrada->quantidade;
            $quantidadeNova = $estoque->qtdTotal + $total;
        } else if(intval($request->quantidade) < $entrada->quantidade){
            $total = $entrada->quantidade - intval($request->quantidade);
            $quantidadeNova = $estoque->qtdTotal - $total;
        } else if(intval($request->quantidade) == $entrada->quantidade){
            $quantidadeNova = $estoque->qtdTotal;
        }

        $validades = json_decode($estoque->validades, true);
        $indiceItem = array_search($entrada->validade, $validades);

        if ($request->validade != $validades[$indiceItem]) {
            unset($validades[$indiceItem]); //Remove do array
            $validades = array_values($validades); //Reorganiza os itens do array

            $novaValidade = $request->validade;//Pega a nova validade

            $validades[] = $novaValidade; //adiciona validade ao array
        } 

        Entrada::where('id', $request->id)->update(
            ['quantidade' => $request->quantidade, 
             'validade' => $request->validade, 
             'estoque_id' => $estoque->id]);

        $estoque = Estoque::where('id', $estoque->id)
                    ->update([
                        'qtdTotal' => $quantidadeNova,
                        'validades' => json_encode($validades),
                        'produto_id' => $request->produto_id,
                    ]);

        return redirect("/admin/entrada");
    }
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
            'name' => 'estoque_id',
            'label' => 'Produto',
            'type' => 'text', 
            'value' => function($entry) {
                $estoque = \App\Models\Estoque::find($entry->estoque_id);
                if ($estoque) {
                    $produto = \App\Models\Produto::find($estoque->produto_id);
                    return $produto->nome; 
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
                $estoque = \App\Models\Estoque::find($entry->estoque_id);
                if ($estoque) {
                    $produto = \App\Models\Produto::find($estoque->produto_id);
                    if($produto){
                        $uf = \App\Models\Uf::find($produto->uf_id);
                        return $uf->uf; 
                    }    
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
