// Tablero 2 x 2: los índices 0..3 se distribuyen así
//   0 1
//   2 3
const FILAS = 2;
const COLUMNAS = 2;

const tablero = document.getElementById("tablero");
const botonReinicio = document.getElementById("reiniciar");

let celdas = [];

function crearTablero() {
  tablero.innerHTML = "";
  celdas = [];
  for (let i = 0; i < FILAS * COLUMNAS; i++) {
    const celda = document.createElement("button");
    celda.type = "button";
    celda.className = "celda";
    celda.setAttribute("aria-label", "Celda " + (i + 1));
    celda.addEventListener("click", () => pulsar(i));
    tablero.appendChild(celda);
    celdas.push(celda);
  }
}

// Devuelve los índices de las celdas adyacentes (arriba, abajo, izquierda, derecha)
function adyacentes(indice) {
  const fila = Math.floor(indice / COLUMNAS);
  const col = indice % COLUMNAS;
  const vecinos = [];
  if (fila > 0) vecinos.push(indice - COLUMNAS);
  if (fila < FILAS - 1) vecinos.push(indice + COLUMNAS);
  if (col > 0) vecinos.push(indice - 1);
  if (col < COLUMNAS - 1) vecinos.push(indice + 1);
  return vecinos;
}

// Al pulsar, la celda y sus adyacentes cambian de color (azul <-> rojo)
function pulsar(indice) {
  celdas[indice].classList.toggle("roja");
  adyacentes(indice).forEach(v => celdas[v].classList.toggle("roja"));
}

function reiniciar() {
  crearTablero();
}

botonReinicio.addEventListener("click", reiniciar);
crearTablero();
