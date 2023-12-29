const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .css('resources/css/app.css', 'public/css');

mix.js('resources/js/dashboard.js', 'public/js');
mix.js('resources/js/graficoColuna.js', 'public/js');
mix.js('resources/js/graficoPizza.js', 'public/js');
