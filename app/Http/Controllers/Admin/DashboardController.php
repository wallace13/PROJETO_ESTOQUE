<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Saida;
use App\Models\Entrada;
use App\Models\Estoque;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function setup()
    {
        $mes = date('m');
        $totalEntradas = Entrada::whereMonth('created_at', $mes)->count();
        $totalSaidas = Saida::whereMonth('created_at', $mes)->count();
        $estoque = Estoque::where('qtdTotal', '>',0)->count();

        // Obtém a data atual
        $dataAtual = Carbon::now();

        // Número de dias desejado (por exemplo, 7 para considerar como próximo)
        $diasDesejados = 7;

        // Consulta para selecionar as entradas com validade próxima
        $validades = Entrada::whereDate('validade', '<=', $dataAtual->addDays($diasDesejados))->take(5)->orderBy('validade', 'asc')->get();

        $ultimasEntradas = Entrada::latest('created_at')->take(5)->get();
        $ultimasSaidas = Saida::latest('created_at')->take(5)->get();
        
        return view('vendor.backpack.ui.dashboard', 
        ['tsaidas' => $totalSaidas, 
        'tentradas' => $totalEntradas, 
        'estoque' => $estoque,
        'saidas' => $ultimasSaidas,
        'entradas' => $ultimasEntradas,
        'validades' => $validades]);
    }
}