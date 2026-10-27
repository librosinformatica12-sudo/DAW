// Variables Vista
const panelConfigurador = document.getElementById("panel-configurador")
const panelsimulador = document.getElementById("panel-simulador")

const anchura_bosque = document.getElementById("spin_anchura_bosque");
const altura_bosque = document.getElementById("spin_altura_bosque");
const probabilidad_arbol = document.getElementById("spin_probabilidad_arbol");
const probabilidad_indencio = document.getElementById("spin_probabilidad_incendio");

const btn_iniciar = document.getElementById("btn_tabla")

// Variable Modelo

let M;
let N;
let P;
let Q;

// Primer arranque
panelConfigurador.style.display = "block";
panelsimulador.style.display = "block";

function prepararTablero() {
    // Variables modelo
    M = parseInt(contenedorM.value);
    N = parseInt(contenedorN.value);
    P = parseFloat(contenedorP.value);
    Q = parseFloat(contenedorQ.value);

    // panelConfigurador.style.display = "block";
    // panelsimulador.style.display = "none";


    // contruccion del tablero automatico con los datos q pongas

    // let tableroDOM = document.createElement("table");
    // tableroDIV.appendChild(tableroDOM)
}

