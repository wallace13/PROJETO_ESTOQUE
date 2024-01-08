import './bootstrap';
import Inputmask from 'inputmask';

var campoCEP = document.getElementsByName('cep')[0];
var campoCNPJ = document.getElementsByName('cnpj')[0];

if (campoCEP != undefined) {
    document.addEventListener("DOMContentLoaded", function() {
        Inputmask("99999-999").mask(campoCEP);
    });
}
if (campoCNPJ != undefined) {
    document.addEventListener("DOMContentLoaded", function() {
        Inputmask("99.999.999/9999-99").mask(campoCNPJ);
    });
}

function aplicarMascara(campoTipo, campoNumero,campoDDD,tipo) {
    function atualizarMascara() {
        if (campoTipo !== undefined) {
            if (campoTipo.value.trim() !== tipo) {
                campoNumero.value = ""; 
                campoDDD.value = "";
            }

            if (campoTipo.value == 2) {
                Inputmask("99999-9999").mask(campoNumero);
            } else if (campoTipo.value == 1 || campoTipo.value == 0) {
                Inputmask("9999-9999").mask(campoNumero);
            }
        }
    }

    document.addEventListener("DOMContentLoaded", atualizarMascara);
    campoTipo.addEventListener("change", atualizarMascara);
}
var idfornecedor = document.getElementsByName('fornecedor_id')[0];

if (idfornecedor !== undefined) {
    var campoTipo = document.getElementsByName('tipo_telefone')[0];
    var campoNumero = document.getElementsByName('numero_telefone')[0];
    var campoDDD = document.getElementsByName('ddd')[0];

    if (campoTipo !== undefined) {
        if (campoTipo.value !== "") {
            var tipoSalvo = campoTipo.value.trim();
        }
        aplicarMascara(campoTipo, campoNumero, campoDDD,tipoSalvo);
    }
}

// Defina a quantidade de campos
var quantidadeCampos = 3;

for (var i = 0; i < quantidadeCampos; i++) {
    var campoTipo = document.getElementsByName('tipo_telefone_' + i)[0];
    var campoNumero = document.getElementsByName('numero_telefone_' + i)[0];
    var campoDDD = document.getElementsByName('ddd_' + i)[0];

    if (campoTipo !== undefined) {
        if (campoTipo.value !== "") {
            var tipoSalvo = campoTipo.value.trim();
        }
        aplicarMascara(campoTipo, campoNumero, campoDDD,tipoSalvo);
    }
    
}

$(document).ready(function () {
    // Evento de mudança no select
    $('select[name="estoque_id"]').on('change', function () {
        // Obter o valor selecionado no select
        var selectedOption = $(this).val();
        if (selectedOption > 0) {
            // Fazer uma chamada Ajax para obter as validades
            $.ajax({
                
                url: 'http://localhost/admin/estoque/'+selectedOption+'/produto', // Substitua com o caminho para o seu script no servidor
                type: 'GET',
                data: { estoque_id: selectedOption },
                success: function (data) {
                    // Limpar opções atuais no select
                    $('select[name="validades"]').empty();

                    // Adicionar uma opção padrão
                    $('select[name="validades"]').append('<option value="">Escolha uma data de validade</option>');

                    // Iterar sobre as datas e adicionar opções ao select
                    for (var i = 0; i < data.length; i++) {
                        var formattedDate = new Date(data[i]['validade']).toLocaleDateString('pt-BR');
                        $('select[name="validades"]').append('<option value="' + data[i]['entrada_id'] + '">' + formattedDate + '</option>');
                    }
                    $('select[name="validades"]').prop('disabled', false);
                },
                error: function () {
                    console.log('Erro ao obter dados do servidor');
                }
            });
        }
    });
});

$(document).ready(function () {
    // Evento de mudança no select "validades"
    $('select[name="validades"]').on('change', function () {
        // Obter o valor selecionado no select
        var selectedDate = $(this).val();
        if (selectedDate) {
            // Fazer uma chamada Ajax para obter a quantidade associada à data de validade
            $.ajax({
                url: 'http://localhost/admin/entrada/' + selectedDate + '/produto',
                type: 'GET',
                data: { entrada_id: selectedDate },
                success: function (data) {
                    // Atualizar o campo de quantidade disponível com os dados recebidos
                    $('input[name="quantidadedisponivel"]').val(data);
                },
                error: function () {
                    console.log('Erro ao obter dados do servidor');
                }
            });
        }
    });
});


$(document).ready(function () {
    // Evento de mudança no select
    $('select[name="estoque_id"]').on('change', function () {
        var selectValidades = $('select[name="validades"]');
        selectValidades.empty();
        selectValidades.append('<option value="">Escolha uma data de validade</option>');
        selectValidades.prop('disabled', true); // Desabilitar o campo quando não há opção selecionada
        var inputQuantidade = $('input[name="quantidadedisponivel"]');
        inputQuantidade.val('');
        
    });
});

$(document).ready(function () {
    // Evento de mudança no select
    $('select[name="validades"]').on('change', function () {
        var inputQuantidade = $('input[name="quantidadedisponivel"]');
        inputQuantidade.val('');
    });
});