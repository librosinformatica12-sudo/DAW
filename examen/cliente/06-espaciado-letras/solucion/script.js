// REFERENCIAS: elementos del HTML
const slider = document.getElementById("exp");
const valor = document.getElementById("valor");
const titulo = document.getElementById("titulo");
const btnMenos = document.getElementById("btn-menos");
const btnMas = document.getElementById("btn-mas");
const btnAnimar = document.getElementById("btn-animar");
const btnReiniciar = document.getElementById("btn-reiniciar");

// VARIABLES: configuración del ejercicio
const MIN = 0;
const MAX = 20;
const PASO = 1;
const ESPACIO_INICIAL = 2;

let espacio = ESPACIO_INICIAL;

// FUNCIÓN: pinta el valor y aplica el espacio entre letras
function pintar() {
    slider.value = espacio;
    valor.textContent = espacio;
    titulo.style.letterSpacing = espacio + "px";
}

// FUNCIÓN: suma o resta sin salirse de los límites
function cambiar(cantidad) {
    espacio = Math.min(MAX, Math.max(MIN, espacio + cantidad));
    pintar();
}

// FUNCIÓN: vuelve al valor inicial
function reiniciar() {
    espacio = ESPACIO_INICIAL;
    pintar();
}

// FUNCIÓN: lanza la animación de latido
function animar() {
    titulo.classList.add("latido");

    // Quita la clase cuando termina la animación para poder repetirla.
    setTimeout(function () {
        titulo.classList.remove("latido");
    }, 900);
}

// EVENTOS: slider y botones
slider.addEventListener("input", function () {
    espacio = Number(slider.value);
    pintar();
});

btnMenos.addEventListener("click", function () {
    cambiar(-PASO);
});

btnMas.addEventListener("click", function () {
    cambiar(PASO);
});

btnAnimar.addEventListener("click", animar);

btnReiniciar.addEventListener("click", reiniciar);

// ARRANQUE: deja la página en su estado inicial
pintar();
