// Variables Vista
const panelConfigurador = document.getElementById("panel-configurador")
const panelSimulador = document.getElementById("panel-simulador")

const tableroDOM = document.getElementById("tablero")
const tableroDIV = document.getElementById("tablero")

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

// Constantes del modelo
const VACIO = 0;
const ARBOL = 1;
const FUEGO = 2;

const CLASES = ["vacio", "arbol", "fuego"];

let bosque;

// Primer arranque
panelConfigurador.style.display = "";
panelSimulador.style.display = "none";

// funcion donde depende de lo q ponga el cliente se hace la tabla
function pintarTablero(){
    // Limpiamos el tablero por si se pulsa "Iniciar" más de una vez
    tableroDIV.innerHTML = "";

    let tablaDOM = document.createElement("table");
    tablaDOM.setAttribute("class", "tablero");

    for(let i = 0; i < M; i++){
        let filaDOM = document.createElement("tr");
        for(let j = 0; j < N; j++){
            let celdaDOM = document.createElement("td");
            celdaDOM.setAttribute("class", CLASES[bosque[i][j]]);
            filaDOM.appendChild(celdaDOM);
        }
        tablaDOM.appendChild(filaDOM);
    }

    tableroDIV.appendChild(tablaDOM);
}

function prepararTablero() {
    // Variables modelo
    M = parseInt(anchura_bosque.value);
    N = parseInt(altura_bosque.value);
    P = parseFloat(probabilidad_arbol.value);
    Q = parseFloat(probabilidad_indencio.value);

    panelConfigurador.style.display = "none";
    panelSimulador.style.display = "";

    // construccion tablero
    bosque = new Array(M);
    for(let i = 0; i < M; i++){
        bosque[i] = new Array(N);
    }

    // Inicializamos el bosque con todas las casillas
    for (let i = 0; i < M; i++) {
        for (let j = 0; j < N; j++){
            bosque[i][j] = (Math.random() < P) ? ARBOL : VACIO;
        }
    }

    pintarTablero();
}

btn_iniciar.addEventListener("click", prepararTablero);