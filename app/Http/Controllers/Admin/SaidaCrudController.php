<?php

namespace App\Http\Controllers\Admin;

use App\Models\Estoque;
use App\Models\Entrada;
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
            ->join('ufs', 'ufs.id', '=', 'produtos.uf_id')
            ->join('entradas', 'entradas.estoque_id', '=', 'estoques.id')
            ->select('ufs.uf','produtos.nome', 'entradas.id', 'entradas.validade','entradas.qtdSaidas','entradas.quantidade')
            ->get();
        $Itens = $estoque->map(function ($produto) {
            if($produto->quantidade != $produto->qtdSaidas){
                return ['id' => $produto->id, 'name' => $produto->nome.' - '.$produto->uf.' - '.date('d/m/Y', strtotime($produto->validade))];
            }            
        })->pluck('name', 'id')->toArray();
        asort($Itens);
        CRUD::field([   // select_from_array
            'name'        => 'estoque_id',//Aqui ele pega o id da entrada
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

        $idEntrada = $request->estoque_id;
        $entrada = Entrada::where('id', $idEntrada)->first();
        $estoque = Estoque::where('id', $entrada->estoque_id)->first();
        
        $totalEntrada = $entrada->quantidade - intval($request->input('quantidade'));
        $totalEstoque = $estoque->qtdTotal - intval($request->input('quantidade'));

        if($totalEntrada == 0){
            $validades = json_decode($estoque->validades, true);
            $indiceItem = array_search($entrada->validade, $validades);//Procura o indice
            unset($validades[$indiceItem]); //Remove do array
            $validades = array_values($validades); //Reorganiza os itens do array
            Estoque::where('id', $entrada->estoque_id)->update(['validades' => json_encode($validades)]);
            $totalEntrada = $entrada->quantidade;
        }
        Entrada::where('id', $idEntrada)->update(['qtdSaidas' => $totalEntrada]);
        Estoque::where('id', $entrada->estoque_id)->update(['qtdTotal' => $totalEstoque]);
        $request['estoque_id'] = $estoque->id;
        $request['entrada_id'] = $entrada->id;

        $entry = $this->crud->create($request->except(['_token', '_method'])); 

        return redirect("/admin/saida");
    }

    protected function setupUpdateOperation()
    {
        $saidaSelecionada = Saida::find($this->crud->getCurrentEntry()->id);
        $estoque = DB::table('estoques')
            ->join('produtos', 'produtos.id', '=', 'estoques.produto_id')
            ->join('ufs', 'ufs.id', '=', 'produtos.uf_id')
            ->join('entradas', 'entradas.estoque_id', '=', 'estoques.id')
            ->select('ufs.uf','produtos.nome', 'entradas.id', 'entradas.validade','entradas.qtdSaidas','entradas.quantidade', 'entradas.estoque_id')
            ->get();        
        $Itens = $estoque->map(function ($produto) use($saidaSelecionada){
            if($produto->id == $saidaSelecionada->entrada_id){
                return ['id' => $produto->id, 'name' => $produto->nome.' - '.$produto->uf.' - '.date('d/m/Y', strtotime($produto->validade))];
            }            
        })->pluck('name', 'id')->toArray();
        asort($Itens);
        CRUD::field([   // select_from_array
            'name'        => 'estoque_id_disable',//Aqui ele pega o id da entrada
            'label'       => "Produto",
            'type'        => 'select_from_array',
            'value'       => $saidaSelecionada->entrada_id, 
            'options'     => $Itens,
            'allows_null' => false,
            'default'     => 'one',
            'attributes' => [
                'disabled'    => 'disabled',
            ],
        ]);  
        $this->setupCreateOperation();
        CRUD::field([
            'name'  => 'estoque_id',
            'type'  => 'hidden',
            'value' => $saidaSelecionada->entrada_id,
        ]);


    }
    public function update()
    {
        $request = $this->crud->validateRequest();
        $saida = Saida::find($request->id);
        $entrada = Entrada::find($request->estoque_id);//estoque_id na real é a entrada id
        $estoque = Estoque::find($entrada->estoque_id);
        
        $total = 0;

        if (intval($request->quantidade) < $entrada->qtdSaidas) {
            $total = $entrada->qtdSaidas - intval($request->quantidade) ;
            $quantidadeNova = $estoque->qtdTotal + $total;
        }else if(intval($request->quantidade) > $entrada->qtdSaidas){
            $total = intval($request->quantidade) - $entrada->qtdSaidas;
            $quantidadeNova = $estoque->qtdTotal - $total;
        }else if(intval($request->quantidade) == $entrada->qtdSaidas){
            $quantidadeNova = $estoque->qtdTotal;
        }

        if($total == 0){
            $validades = json_decode($estoque->validades, true);
            $indiceItem = array_search($entrada->validade, $validades);//Procura o indice
            unset($validades[$indiceItem]); //Remove do array
            $validades = array_values($validades); //Reorganiza os itens do array
        }else if(intval($request->quantidade) != $entrada->qtdSaidas){
            $validades = json_decode($estoque->validades, true);
            $novaValidade = $entrada->validade;
            $validades[] = $novaValidade;
        }
        
        Saida::where('id', $request->id)->update(['quantidade' => intval($request->quantidade)]);
        Entrada::where('id', $entrada->id)->update(['qtdSaidas' => intval($request->quantidade)]);
        Estoque::where('id', $estoque->id)->update(['qtdTotal' => $quantidadeNova, 'validades' => json_encode($validades)]);

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

