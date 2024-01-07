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