// REFERENCIAS: elementos del HTML
const slider = document.getElementById("exp");
const valor = document.getElementById("valor");
const galeria = document.getElementById("galeria");
const cajas = document.querySelectorAll(".caja");
const btnMenos = document.getElementById("btn-menos");
const btnMas = document.getElementById("btn-mas");
const btnAnimar = document.getElementById("btn-animar");
const btnReiniciar = document.getElementById("btn-reiniciar");

// VARIABLES: configuración del ejercicio
const MIN = 0;
const MAX = 40;
const PASO = 4;
const ESPACIO_INICIAL = 8;

let espacio = ESPACIO_INICIAL;

// FUNCIÓN: pinta el valor y aplica el espacio entre tarjetas
function pintar() {
    slider.value = espacio;
    valor.textContent = espacio;
    galeria.style.gap = espacio + "px";
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

// FUNCIÓN: lanza la animación de balanceo en cadena
function animar() {
    cajas.forEach(function (caja, indice) {
        // Quita la clase y fuerza el reinicio de la animación.
        caja.classList.remove("balanceo");
        void caja.offsetWidth;

        // Cada tarjeta empieza un poco después que la anterior.
        caja.style.animationDelay = (indice * 0.08) + "s";
        caja.classList.add("balanceo");

        // Limpia la clase cuando termina su animación.
        setTimeout(function () {
            caja.classList.remove("balanceo");
            caja.style.animationDelay = "";
        }, 900 + indice * 80);
    });
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
