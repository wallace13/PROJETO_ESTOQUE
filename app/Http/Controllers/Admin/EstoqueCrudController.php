<?php

namespace App\Http\Controllers\Admin;

use App\Models\Entrada;
use App\Models\Estoque;
use App\Models\Produto;
use App\Models\Uf;
use App\Http\Requests\EstoqueRequest;
use Illuminate\Support\Facades\DB;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Mockery\Undefined;

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
    protected function setupShowOperation()
    {
        $this->setupCommonColumns();
        CRUD::addColumn([
            'name' => 'validades',
            'label' => 'Validades',
            'type' => 'json',
            'searchLogic' => true,
            'orderable' => true,
            'visibleInModal' => true,
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
        $estoques = DB::table('produtos')
        ->select('estoques.produto_id','produtos.nome', 'temp.qtdTotal', DB::raw('GROUP_CONCAT(entradas.validade) as validades'), 'ufs.uf')
        ->join(DB::raw('(SELECT estoques.produto_id, SUM(estoques.qtdTotal) as qtdTotal FROM estoques
            GROUP BY estoques.produto_id) as temp'), 'produtos.id', '=', 'temp.produto_id')
        ->join('estoques', 'produtos.id', '=', 'estoques.produto_id')
        ->join('entradas', 'entradas.estoque_id', '=', 'estoques.id')
        ->join('ufs', 'ufs.id', '=', 'produtos.uf_id') 
        ->groupBy('produtos.nome', 'temp.qtdTotal', 'ufs.uf','estoques.produto_id')
        ->get();
        CRUD::addColumn([
            'name' => 'produto_id',
            'label' => 'Produto',
            'type' => 'text', 
            'value' => function($entry) {
                $estoque = Estoque::with('produto.ufs')->findOrFail($entry->id);
                if ($estoque) {
                    return $estoque->produto->nome; 
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
                $estoque = Estoque::with('produto.ufs')->findOrFail($entry->id);
                if ($estoque) {
                    return $estoque->produto->ufs->uf; 
                }
                return 'Uf não encontrado';
            },
            'searchLogic'    => true,
            'orderable'      => true,
            'visibleInModal' => true,
        ]);
        CRUD::addColumn([
            'name' => 'categoria',
            'label' => 'Categoria',
            'type' => 'text',
            'value' => function($entry) {
                $estoque = Estoque::with('produto.categorias')->findOrFail($entry->id);
                if ($estoque) {
                    return $estoque->produto->categorias->descricao; 
                }
                return 'Categoria não encontrado';
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
    public static function store($request)
    {
        $estoque = Estoque::firstOrNew(['produto_id' => $request->produto_id]);

        $estoque->qtdTotal = ($estoque->exists) ?  $estoque->qtdTotal += $request->quantidade : $request->quantidade;

        $validades = self::updateValidades(null,$estoque,$request);
        $estoque->validades = $estoque->encodeValidadesJSON($validades);
        $estoque->save();
        
        return $estoque->id;
    }
    public static function updateEstoque($entrada, $request)
    {
        $estoque = Estoque::find($entrada->estoque_id);

        $quantidadeNova = $estoque->atualizarQuantidadeEntrada($request->quantidade, $entrada->quantidade);

        self::updateValidades($entrada,$estoque,$request);
        
        $estoque->update(['qtdTotal' => $quantidadeNova,'produto_id' => $request->produto_id]);
        return $estoque->id;
    }
    public static function updateValidades($entrada, $estoque, $request)
    {
        $validades = $estoque->decodeValidadesJSON($estoque->validades);
        if($validades === null || empty($validades) && $request !== null){
            $validades[] = $estoque->criaArrayValidades($request->validade);
            if($entrada === null){
                return $validades;
            }
        }else{
            //dd($entrada, $estoque, $request);
            if($entrada !== null){
                $indiceItem = $estoque->buscaValidadeNoArray($entrada->validade, $validades);
                $validade = $entrada->validade;
                $qtdValidades = $estoque->countValidadeEntrada($entrada);
                
                if ($indiceItem !== false && ($qtdValidades <= 1 || ($request['total'] == 0 && $request['total'] !== null))) {
                    $validades = $estoque->removeValidade($indiceItem, $validades);
                }
            }
            if($request !== null && $request['total'] === null){
                $indiceItem = $estoque->buscaValidadeNoArray($request->validade, $validades);
                $validade = $request->validade;
            }

            if ($indiceItem === false && ($request === null || $request['total'] === null)) {
                $validades[] = $estoque->criaArrayValidades($validade);
            }

            if ($entrada !== null && $request !== null){
                if ($indiceItem === false && (intval($request->quantidade) != $entrada->qtdSaidas) && $request['total'] !== null) {
                    $validades[] = $estoque->criaArrayValidades($validade);
                }
            }

            if ($request !== null && $entrada === null) {
                return $validades;
            }            
        }
        $estoque->where('id', $entrada->estoque_id)->update(['validades' => json_encode($validades)]);
    }
    public static function atualizarQuantidadesSaida($request, $entrada, $estoque)
    {
        $totalEntrada = $entrada->quantidade - $entrada->qtdSaidas - intval($request->input('quantidade'));
        $totalEstoque = $estoque->qtdTotal - intval($request->input('quantidade'));

        if ($totalEntrada == 0) {
            self::updateValidades($entrada,$estoque,null);
            $qtdSaidas = $entrada->quantidade;
        } else {
            $qtdSaidas = $entrada->qtdSaidas + intval($request->input('quantidade'));
        }

        $entrada->update(['qtdSaidas' => $qtdSaidas]);
        $estoque->update(['qtdTotal' => $totalEstoque]);
    }

    public static function removeQuantidade($estoque, $entrada, $saida){
        if ($entrada != null) {
            $quantidadeNova = $estoque->qtdTotal - $entrada->quantidade;
        }
        if ($saida != null) {
            $quantidadeNova = $estoque->qtdTotal + $saida->quantidade;
        }

        $estoque->update(['qtdTotal' => $quantidadeNova]);
    }
            

}
