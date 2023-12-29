import GraficoColuna from "./graficoColuna";
import GraficoPizza from "./graficoPizza";

let dadosGraficoEntradaSaida = { data: [] };

let graficoColunaEntradaSaida = null;
let graficoPizzzaEntradaSaida = null;

const entradas = document.getElementById('entradas').innerText;
const saidas = document.getElementById('saidas').innerText;

dadosGraficoEntradaSaida.data = [entradas,saidas];

// Gráfico de coluna - Entrada/Saida
const colunaEntradaSaida = document.getElementById('colunaEntradaSaida').getContext('2d');
graficoColunaEntradaSaida = GraficoColuna(colunaEntradaSaida, dadosGraficoEntradaSaida.data);

// Criação do gráfico de pizza Entrada/Saida
const pizzaEntradaSaida = document.getElementById('pizzaEntradaSaida').getContext('2d');
graficoPizzzaEntradaSaida = GraficoPizza(pizzaEntradaSaida, dadosGraficoEntradaSaida.data);

    