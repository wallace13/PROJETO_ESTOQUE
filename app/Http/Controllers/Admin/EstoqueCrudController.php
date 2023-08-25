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
            'name' => 'qtdTotal',
            'label' => 'Quantidade',
            'type' => 'text',
            'searchLogic'    => true,
            'orderable'      => true,
            'visibleInModal' => true,
        ]);
    }
}
