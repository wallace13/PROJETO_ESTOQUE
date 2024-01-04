<?php

namespace App\Http\Controllers\Admin;

use App\Models\Estoque;
use Illuminate\Support\Facades\DB;
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
                return $entry->produto->nome; 
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
                return $entry->produto->ufs->uf; 
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
                return $entry->produto->categorias->descricao; 
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
    public function store($request)
    {
        $estoque = Estoque::firstOrNew(['produto_id' => $request->produto_id]);

        $estoque->qtdTotal = ($estoque->exists) ?  $estoque->qtdTotal += $request->quantidade : $request->quantidade;

        $validades = $this->verificaValidade($estoque, $request->validade);

        $estoque->validades = $estoque->encodeValidadesJSON($validades);
        $estoque->save();
        
        return $estoque->id;
    }
    public function update($entrada, $request)
    {
        $estoque = Estoque::find($entrada->estoque_id);

        $quantidadeNova = $estoque->atualizarQuantidadeEntrada($request->quantidade, $entrada->quantidade);

        $this->verificaQuantidadeValidade($estoque, $entrada);
        
        $validades = $this->verificaValidade($estoque, $request->validade);

        $estoque->update([
            'qtdTotal' => $quantidadeNova,
            'validades' => $validades, 
            'produto_id' => $request->produto_id
        ]);
        return $estoque->id;
    }

    private function verificaQuantidadeValidade($estoque, $entrada){
        $quantidadeValidade = $entrada->countValidadeEntrada($entrada->validade);

        if($quantidadeValidade <= 1){
            $this->removeValidade($estoque, $entrada->validade); 
        }
    }

    private function readJsonValidades($estoque){
        return $estoque->decodeValidadesJSON();
    }

    private function buscaValidade($estoque, $validadeBuscada){
        $indiceItem = $estoque->buscaValidadeNoArray($validadeBuscada);
        return $indiceItem;
    }

    private function verificaValidade($estoque, $validadePassada){
        $validades = $this->readJsonValidades($estoque);
        $resultado = $this->buscaValidade($estoque, $validadePassada);

        if ($resultado === false && empty($validades)) {
            $validades[] = $estoque->criaArrayValidades($validadePassada);
            return $validades;
        }

        if ($resultado === false && !empty($validades)) {
            $validadesNovas = $estoque->adicionaValidadeNoArray($validadePassada);
            return $validadesNovas;
        }
        return $validades;
    }
    public function voltaValidade($estoque, $validadeRemovida)
    {
        $validades = $this->verificaValidade($estoque, $validadeRemovida);
        $this->updateValidade($estoque, $validades);
    }

    public function removeValidade($estoque, $validadeRemovida)
    {
        $indiceItem = $estoque->buscaValidadeNoArray($validadeRemovida);
        $validades = $estoque->removeValidade($indiceItem);
        $this->updateValidade($estoque, $validades);
    }

    public function removeQuantidade($request, $estoque)
    {
        $totalEstoque = $estoque->removeQuantidade($request->input('quantidade'));
        self::updateQuantidade($estoque, $totalEstoque);
    }

    private function updateValidade($estoque, $validades)
    {
        $estoque->update(['validades' => $validades]);
    }

    public static function updateQuantidade($estoque, $quantidade)
    {
        $estoque->update(['qtdTotal' => $quantidade]);
    }
            

}
