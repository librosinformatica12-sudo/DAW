// ============================================================
// TIRO CON ARCO - Versión SIMPLE (bajita)
// Solo lo imprescindible: diana que se mueve, disparar y puntuar.
// ============================================================

const escenario = document.getElementById('escenario');
const dianaDIV  = document.getElementById('diana');
const flechaDIV = document.getElementById('flecha');
const btnDisparar  = document.getElementById('btn_disparar');
const btnReiniciar = document.getElementById('btn_reiniciar');

const ANCHO_ESCENARIO = 600;
const ALTO_ESCENARIO  = 450;
const ANCHO_DIANA     = 100;
const LARGO_FLECHA    = 70;
const Y_IMPACTO       = 370;

let flechasRestantes = 5;
let puntos = 0;
let aciertos = 0;
let fallos = 0;

let dianaX = 0;
let dianaDireccion = 1;
let flechaY = 70;
let flechaVolando = false;
let impactoComprobado = false;

let timerDiana = null;
let timerFlecha = null;

btnDisparar.addEventListener('click', disparar);
btnReiniciar.addEventListener('click', reiniciar);

// --- La diana se mueve siempre ---
function iniciarDiana()
{
    clearInterval(timerDiana);
    timerDiana = setInterval(function ()
    {
        dianaX += 4 * dianaDireccion;

        if (dianaX >= ANCHO_ESCENARIO - ANCHO_DIANA)
        {
            dianaX = ANCHO_ESCENARIO - ANCHO_DIANA;
            dianaDireccion = -1;
        }
        if (dianaX <= 0)
        {
            dianaX = 0;
            dianaDireccion = 1;
        }

        dianaDIV.style.left = dianaX + 'px';
    }, 40);
}

// --- Disparar ---
function disparar()
{
    if (flechaVolando || flechasRestantes <= 0) return;

    flechaVolando = true;
    impactoComprobado = false;
    flechasRestantes--;

    flechaY = 70;
    flechaDIV.style.bottom = flechaY + 'px';
    flechaDIV.style.display = 'block';
    btnDisparar.disabled = true;

    actualizar();
    timerFlecha = setInterval(volarFlecha, 20);
}

// --- Vuelo ---
function volarFlecha()
{
    flechaY += 10;
    flechaDIV.style.bottom = flechaY + 'px';

    if (!impactoComprobado && flechaY + LARGO_FLECHA >= Y_IMPACTO)
    {
        impactoComprobado = true;
        comprobar();
    }

    if (flechaY > ALTO_ESCENARIO)
    {
        terminar();
    }
}

// --- Puntuación ---
function comprobar()
{
    const centroDiana = dianaX + ANCHO_DIANA / 2;
    const distancia = Math.abs(centroDiana - ANCHO_ESCENARIO / 2);

    let puntosTiro = 0;
    if (distancia <= 10)      puntosTiro = 10;
    else if (distancia <= 25) puntosTiro = 5;
    else if (distancia <= 50) puntosTiro = 1;

    if (puntosTiro > 0)
    {
        aciertos++;
        puntos += puntosTiro;
        document.getElementById('mensaje').innerText = '¡Diana! +' + puntosTiro;
        actualizar();
        terminar();
    }
    else
    {
        fallos++;
        document.getElementById('mensaje').innerText = 'Has fallado...';
        actualizar();
    }
}

// --- Fin del vuelo ---
function terminar()
{
    clearInterval(timerFlecha);
    flechaDIV.style.display = 'none';
    flechaVolando = false;

    if (flechasRestantes > 0)
    {
        btnDisparar.disabled = false;
    }
    else
    {
        document.getElementById('mensaje').innerText =
            'Fin. Puntos: ' + puntos;
        btnDisparar.disabled = true;
    }
}

// --- Pintar números ---
function actualizar()
{
    document.getElementById('flechas').innerText  = flechasRestantes;
    document.getElementById('puntos').innerText   = puntos;
    document.getElementById('aciertos').innerText = aciertos;
    document.getElementById('fallos').innerText   = fallos;
}

// --- Reiniciar todo ---
function reiniciar()
{
    clearInterval(timerFlecha);
    flechasRestantes = 5;
    puntos = 0;
    aciertos = 0;
    fallos = 0;
    dianaX = 0;
    dianaDireccion = 1;
    flechaVolando = false;

    dianaDIV.style.left = '0px';
    flechaDIV.style.display = 'none';
    btnDisparar.disabled = false;
    document.getElementById('mensaje').innerText = 'Pulsa Disparar';

    actualizar();
    iniciarDiana();
}

// --- Arranque ---
actualizar();
iniciarDiana();
