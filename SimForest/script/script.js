// Variables Vista
const panelConfigurador = document.getElementById("panel-configurador")
const panelSimulador = document.getElementById("cabezera")

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

// Primer arranque
panelConfigurador.style.display = "block";
panelSimulador.style.display = "none";

// funcion donde depende de lo q ponga el cliente se hace la tabla
function pintarTablero(){
    let tablaDOM = document.createElement("table");
    tableroDOM.setAttribute("class","tablero")

    for(let i = 0; i < M; i++){
        let filaDOM = document.createElement("tr");
        for(let j = 0; j < N; j++){
            let celdaDOM = document.createElement("td");
            filaDOM
        }
    }



    tableroDIV.appendChild(tablaDOM)

}

function prepararTablero() {
    // Variables modelo
    M = parseInt(contenedorM.value);
    N = parseInt(contenedorN.value);
    P = parseFloat(contenedorP.value);
    Q = parseFloat(contenedorQ.value);

    panelConfigurador.style.display = "none";
    panelsimulador.style.display = "block";

    // construccion tablero
    bosque = new Array(M);
    for(let i = 0; i < M; i++){
        bosque[i] = new Array(N);
    }

    // Inicializamos el bosque con todas las casiilas
    for (let i = 0; i < M; i++) {

    }


    
}

btn_iniciar.addEventListener("click", (prepararTablero));
