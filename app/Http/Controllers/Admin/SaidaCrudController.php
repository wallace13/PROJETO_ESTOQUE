<?php

namespace App\Http\Controllers\Admin;

use App\Models\Estoque;
use App\Models\Produto;
use App\Models\Saida;
use App\Http\Requests\SaidaRequest;
use Illuminate\Support\Facades\DB;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class SaidaCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
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
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(SaidaRequest::class);
        $estoque = DB::table('estoques')
            ->join('produtos', 'produtos.id', '=', 'estoques.produto_id')
            ->select('produtos.*', 'estoques.*')
            ->where('estoques.qtdTotal', '>', 0)
            ->get();
        $Itens = $estoque->map(function ($produto) {
            $uf = DB::table('ufs')->select('uf')->where('id', $produto->uf_id)->get();
            return ['id' => $produto->produto_id, 'name' => $produto->nome.' - '.$uf[0]->uf];
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
            'label'     => "Quantidade",
            'type'      => 'text',
            'name'      => 'quantidade',
        ]);     
    }

    public function store()
    {
        $request = $this->crud->validateRequest();
        $produto_id = $request->input('produto_id');
        $estoque = Estoque::select('id', 'qtdTotal')->where('produto_id', $produto_id)->first();
        $total = $estoque->qtdTotal - intval($request->input('quantidade'));
        Estoque::where('id', $estoque->id)->update(['qtdTotal' => $total]);
        $request['estoque_id'] = $estoque->id;
        $entry = $this->crud->create($request->except(['_token', '_method'])); 

        return redirect("/admin/saida");
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();

        $estoqueSelecionado = Estoque::find($this->crud->getCurrentEntry()->estoque_id);
        $estoque = DB::table('estoques')
            ->join('produtos', 'produtos.id', '=', 'estoques.produto_id')
            ->select('produtos.*', 'estoques.*')
            ->get();
        $Itens = $estoque->map(function ($produto) {
            $uf = DB::table('ufs')->select('uf')->where('id', $produto->uf_id)->get();
            return ['id' => $produto->produto_id, 'name' => $produto->nome.' - '.$uf[0]->uf];
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
        
        $saida = Saida::find($request->id);
        $estoque = Estoque::where('produto_id', $request->produto_id)->get();
        if (intval($request->quantidade) > $saida->quantidade) {
            $total = $estoque[0]->qtdTotal - intval($request->quantidade);
        } else {
            $total = $saida->quantidade - intval($request->quantidade);
        }
        
        Saida::where('id', $request->id)->update(
            ['quantidade' => $request->quantidade,
             'estoque_id' => $estoque[0]->id]);
        
        $estoque = Estoque::where('id', $estoque[0]->id)
                    ->update(['qtdTotal' => $total]);

        return redirect("/admin/saida");
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
            'name' => 'quantidade',
            'label' => 'Quantidade',
            'type' => 'text',
            'searchLogic'    => true,
            'orderable'      => true,
            'visibleInModal' => true,
        ]);
    }
}

