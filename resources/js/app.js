import './bootstrap';
import Inputmask from 'inputmask';

var campoTelefone = document.getElementsByName('telefone')[0];
var campoCEP = document.getElementsByName('cep')[0];
var campoCNPJ = document.getElementsByName('cnpj')[0];

if (campoTelefone != undefined) {
    document.addEventListener("DOMContentLoaded", function() {
        Inputmask("(99) 9999-9999").mask(campoTelefone);
    });
}
if (campoCEP != undefined) {
    document.addEventListener("DOMContentLoaded", function() {
        Inputmask("9999-999").mask(campoCEP);
    });
}
if (campoCNPJ != undefined) {
    document.addEventListener("DOMContentLoaded", function() {
        Inputmask("99.999.999/9999-99").mask(campoCNPJ);
    });
}