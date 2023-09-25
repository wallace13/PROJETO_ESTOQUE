<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { 
    Route::crud('produto', 'ProdutoCrudController');
    Route::crud('estoque', 'EstoqueCrudController');
    Route::crud('uf', 'UfCrudController');
    Route::crud('entrada', 'EntradaCrudController');
    Route::crud('saida', 'SaidaCrudController');
    Route::get('dashboard', 'DashboardController@Setup')->name('dashboard');
    Route::crud('activitylog', 'ActivitylogCrudController');
    
    Route::get('saida/{id}/cancelar', 'SaidaCrudController@cancelarSaida');
    Route::get('entrada/{id}/cancelar', 'EntradaCrudController@cancelarEntrada');

    Route::get('produto/{id}/remover', 'ProdutoCrudController@remover');
    Route::get('uf/{id}/remover', 'UfCrudController@remover');
}); 