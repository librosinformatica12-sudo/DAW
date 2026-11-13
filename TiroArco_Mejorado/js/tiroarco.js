// ============================================================
// TIRO CON ARCO - Versión MEJORADA
// Mejoras respecto al original:
//   1. La flecha se queda clavada en la diana al acertar.
//   2. La pausa es real: acelerar/frenar no reanudan si está en pausa.
//   3. Se valida también el máximo de flechas (20).
//   4. Se puede disparar con la barra ESPACIADORA.
//   5. Récord de la sesión.
//   6. Muestra la velocidad actual en milisegundos.
//   7. Al terminar la partida la diana se detiene sola.
// ============================================================

// --- Referencias al DOM ---
const cabecera          = document.getElementById('cabecera');
const panelConfigurador = document.getElementById('panel-config');
const panelJuego        = document.getElementById('panel-juego');
const escenario         = document.getElementById('escenario');
const dianaDIV          = document.getElementById('diana');
const flechaDIV         = document.getElementById('flecha');
const arcoDIV           = document.getElementById('arco');
const inputFlechas      = document.getElementById('spin_flechas');
const inputVelocidad    = document.getElementById('spin_velocidad');
const btnIniciar        = document.getElementById('btn_iniciar_juego');
const btnDisparar       = document.getElementById('btn_disparar');
const btnPlay           = document.getElementById('btn_play');
const btnStop           = document.getElementById('btn_stop');
const btnAcelerar       = document.getElementById('btn_acelerar');
const btnFrenar         = document.getElementById('btn_frenar');
const btnReiniciar      = document.getElementById('btn_reiniciar');

// --- Constantes (deben coincidir con el CSS) ---
const ANCHO_ESCENARIO = 600;
const ALTO_ESCENARIO  = 450;
const ANCHO_DIANA     = 100;
const LARGO_FLECHA    = 70;
const Y_IMPACTO       = 370;

// --- Estado del juego ---
let flechasRestantes = 0;
let puntos = 0;
let aciertos = 0;
let fallos = 0;
let record = 0;

let dianaX = 0;
let dianaDireccion = 1;
let velocidad = 60;

let flechaY = 70;
let flechaVolando = false;
let impactoComprobado = false;
let flechaClavada = false;

let pausado = false;
let partidaTerminada = false;

let timerDiana = null;
let timerFlecha = null;

// --- Arranque ---
panelConfigurador.style.display = 'block';
panelJuego.style.display = 'none';

// --- Eventos ---
btnIniciar.addEventListener('click', prepararJuego);
btnDisparar.addEventListener('click', disparar);
btnPlay.addEventListener('click', playDiana);
btnStop.addEventListener('click', stopDiana);
btnAcelerar.addEventListener('click', acelerar);
btnFrenar.addEventListener('click', frenar);
btnReiniciar.addEventListener('click', reiniciar);

// Mejora 4: disparar con la barra espaciadora
document.addEventListener('keydown', function (evento)
{
    if (evento.code === 'Space' && panelJuego.style.display !== 'none')
    {
        evento.preventDefault(); // evita que la página haga scroll
        disparar();
    }
});

// ============================================================
// TEMPORIZADORES (con pausa real)
// ============================================================
function playDiana()
{
    if (partidaTerminada) return;

    clearInterval(timerDiana);
    pausado = false;
    timerDiana = setInterval(moverDiana, velocidad);
}

function stopDiana()
{
    clearInterval(timerDiana);
    timerDiana = null;
    pausado = true;
}

function acelerar()
{
    velocidad = Math.max(10, velocidad - 5);
    actualizarVelocidad();

    // Mejora 2: si estaba en pausa, NO reanudamos; solo cambia la velocidad
    if (!pausado) playDiana();
}

function frenar()
{
    velocidad = Math.min(100, velocidad + 5);
    actualizarVelocidad();

    if (!pausado) playDiana();
}

// ============================================================
// MOVIMIENTO DE LA DIANA
// ============================================================
function moverDiana()
{
    dianaX += 4 * dianaDireccion;

    if (dianaX >= ANCHO_ESCENARIO - ANCHO_DIANA)
    {
        dianaX = ANCHO_ESCENARIO - ANCHO_DIANA;
        dianaDireccion = -1;
    }
    else if (dianaX <= 0)
    {
        dianaX = 0;
        dianaDireccion = 1;
    }

    dianaDIV.style.left = dianaX + 'px';
}

// ============================================================
// DISPARO Y VUELO
// ============================================================
function disparar()
{
    if (flechaVolando || flechasRestantes <= 0 || partidaTerminada) return;

    flechaClavada = false;
    flechaVolando = true;
    impactoComprobado = false;
    flechasRestantes--;

    flechaY = 70;
    flechaDIV.style.bottom = flechaY + 'px';
    flechaDIV.style.display = 'block';
    btnDisparar.disabled = true;

    arcoDIV.classList.remove('disparo');
    void arcoDIV.offsetWidth;
    arcoDIV.classList.add('disparo');

    actualizarEstadisticas();
    timerFlecha = setInterval(volarFlecha, 10);
}

function volarFlecha()
{
    flechaY += 8;
    flechaDIV.style.bottom = flechaY + 'px';

    if (!impactoComprobado && flechaY + LARGO_FLECHA >= Y_IMPACTO)
    {
        impactoComprobado = true;
        comprobarImpacto();
    }

    if (flechaY > ALTO_ESCENARIO)
    {
        terminarVuelo(false);
    }
}

// ============================================================
// PUNTUACIÓN
// ============================================================
function comprobarImpacto()
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
        animarImpacto(puntosTiro);

        // Mejora 1: la flecha se queda clavada en la diana
        flechaClavada = true;
        actualizarEstadisticas();
        terminarVuelo(true);
    }
    else
    {
        fallos++;
        document.getElementById('mensaje').innerText = 'Has fallado...';
        actualizarEstadisticas();
    }
}

function animarImpacto(puntosTiro)
{
    dianaDIV.classList.remove('impacto');
    void dianaDIV.offsetWidth;
    dianaDIV.classList.add('impacto');

    const flotante = document.createElement('span');
    flotante.className = 'punto-flotante';
    flotante.innerText = '+' + puntosTiro;
    flotante.style.left = (dianaX + 35) + 'px';
    flotante.style.top = '20px';
    escenario.appendChild(flotante);

    setTimeout(() => flotante.remove(), 1000);
}

// ============================================================
// FIN DE VUELO / ESTADÍSTICAS
// ============================================================
function terminarVuelo(clavada)
{
    clearInterval(timerFlecha);
    timerFlecha = null;

    // Mejora 1: si acertó, dejamos la flecha visible (clavada)
    if (!clavada) flechaDIV.style.display = 'none';

    flechaVolando = false;

    if (flechasRestantes > 0)
    {
        btnDisparar.disabled = false;
    }
    else
    {
        finalizarPartida();
    }
}

function finalizarPartida()
{
    partidaTerminada = true;
    btnDisparar.disabled = true;
    stopDiana(); // Mejora 7: la diana deja de moverse

    // Mejora 5: actualizar récord
    if (puntos > record)
    {
        record = puntos;
        actualizarRecord();
    }

    document.getElementById('mensaje').innerText =
        'Fin de la partida. Puntos: ' + puntos + ' · Récord: ' + record;
}

function actualizarEstadisticas()
{
    document.getElementById('n_flechas').innerText  = flechasRestantes;
    document.getElementById('n_puntos').innerText   = puntos;
    document.getElementById('n_aciertos').innerText = aciertos;
    document.getElementById('n_fallos').innerText   = fallos;
}

function actualizarVelocidad()
{
    document.getElementById('n_velocidad').innerText = velocidad;
}

function actualizarRecord()
{
    document.getElementById('n_record').innerText = record;
}

// ============================================================
// PREPARAR / REINICIAR
// ============================================================
function prepararJuego()
{
    let numFlechas = parseInt(inputFlechas.value);
    let nivel = parseInt(inputVelocidad.value);

    if (isNaN(numFlechas) || numFlechas < 1) numFlechas = 5;
    if (numFlechas > 20) numFlechas = 20; // Mejora 3: tope máximo
    if (isNaN(nivel) || nivel < 1 || nivel > 10) nivel = 5;

    velocidad = 110 - nivel * 10;

    flechasRestantes = numFlechas;
    puntos = 0;
    aciertos = 0;
    fallos = 0;
    dianaX = 0;
    dianaDireccion = 1;
    flechaVolando = false;
    flechaClavada = false;
    pausado = false;
    partidaTerminada = false;

    flechaDIV.style.display = 'none';
    dianaDIV.style.left = '0px';
    btnDisparar.disabled = false;
    document.getElementById('mensaje').innerText = '¡Pulsa Disparar!';

    panelConfigurador.style.display = 'none';
    cabecera.style.display = 'none';
    panelJuego.style.display = 'flex';

    actualizarEstadisticas();
    actualizarVelocidad();
    playDiana();
}

function reiniciar()
{
    stopDiana();
    clearInterval(timerFlecha);
    timerFlecha = null;
    partidaTerminada = false;

    flechaDIV.style.display = 'none';

    panelJuego.style.display = 'none';
    panelConfigurador.style.display = 'block';
    cabecera.style.display = 'block';
}
