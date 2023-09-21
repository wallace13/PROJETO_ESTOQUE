<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Saida;
use App\Models\Entrada;
use App\Models\Estoque;

class DashboardController extends Controller
{
    public function setup()
    {
        $mes = date('m');
        $totalEntradas = Entrada::whereMonth('created_at', $mes)->count();
        $totalSaidas = Saida::whereMonth('created_at', $mes)->count();
        $estoque = Estoque::where('qtdTotal', '>',0)->count();

        $ultimasEntradas = Entrada::latest('created_at')->take(3)->get();
        $ultimasSaidas = Saida::latest('created_at')->take(3)->get();
        
        return view('vendor.backpack.ui.dashboard', 
        ['tsaidas' => $totalSaidas, 
        'tentradas' => $totalEntradas, 
        'estoque' => $estoque,
        'saidas' => $ultimasSaidas,
        'entradas' => $ultimasEntradas]);
    }
}
