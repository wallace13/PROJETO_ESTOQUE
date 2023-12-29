import { Chart } from "chart.js/auto";

function GraficoColuna(ctx, data) {
    const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Entradas', 'Saidas'],
            datasets: [{
                data: data,
                backgroundColor: [
                    'rgba(158, 197, 254, 1)',//Azul
                    'rgba(241, 174, 181, 1)', //Vermelho
                ],
                borderColor: [
                    'rgba(158, 197, 254, 1)',//Azul
                    'rgba(241, 174, 181, 1)', //Vermelho
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false // Isso remove a legenda do gráfico
                }
            },
            maintainAspectRatio: false,
            responsive: true,
            aspectRatio: 1.5,
        }
    });
    return myChart;
}
export default GraficoColuna;
