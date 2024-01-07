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

function aplicarMascara(campoTipo, campoNumero) {
    function atualizarMascara() {
        if (campoTipo !== undefined) {
            // Limpar o campo de número se o tipo for alterado
            campoNumero.value = "";

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

// Defina a quantidade de campos
var quantidadeCampos = 3;

for (var i = 0; i < quantidadeCampos; i++) {
    var campoTipo = document.getElementsByName('tipo_telefone_' + i)[0];
    var campoNumero = document.getElementsByName('numero_telefone_' + i)[0];
    
    aplicarMascara(campoTipo, campoNumero);
}