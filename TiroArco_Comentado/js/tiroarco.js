/* ============================================================
   TIRO CON ARCO - LÓGICA (JS) TOTALMENTE COMENTADA
   ============================================================
   CÓMO ESTUDIAR ESTE ARCHIVO (orden mental):
   1) Referencias al DOM (buscar los elementos)
   2) Constantes del escenario
   3) Variables del MODELO (el "estado" del juego)
   4) Arranque (ocultar/mostrar paneles) + eventos
   5) Temporizadores (setInterval / clearInterval)
   6) Funciones de animación (moverDiana, disparar, volarFlecha...)
   7) Puntuación (comprobarImpacto)
   8) Estadísticas y preparar/reiniciar partida

   PATRÓN MODELO-VISTA:
   - VISTA  = los elementos del HTML (dianaDIV, flechaDIV, ...)
   - MODELO = variables con números (dianaX, puntos, flechasRestantes...)
   La vista NUNCA se lee para calcular: se usan las variables del modelo.
   ============================================================ */

// ============================================================
// 1) VARIABLES DE VISTA (referencias a elementos del HTML)
// ============================================================
// getElementById busca en el documento el elemento con ese id.
// Se hace una sola vez al cargar (por eso el <script> lleva defer).
const cabecera          = document.getElementById('cabecera');
const panelConfigurador = document.getElementById('panel-config');
const panelJuego        = document.getElementById('panel-juego');
const escenario         = document.getElementById('escenario');
const dianaDIV          = document.getElementById('diana');
const flechaDIV         = document.getElementById('flecha');
const arcoDIV           = document.getElementById('arco');

// Estos dos son <input type="number">; su valor se lee con .value
const contenedorFlechas   = document.getElementById('spin_flechas');
const contenedorVelocidad = document.getElementById('spin_velocidad');

// Botones
const btn_iniciar   = document.getElementById('btn_iniciar_juego');
const btn_disparar  = document.getElementById('btn_disparar');
const btn_play      = document.getElementById('btn_play');
const btn_stop      = document.getElementById('btn_stop');
const btn_acelerar  = document.getElementById('btn_acelerar');
const btn_frenar    = document.getElementById('btn_frenar');
const btn_reiniciar = document.getElementById('btn_reiniciar');

// ============================================================
// 2) CONSTANTES DEL ESCENARIO
// ============================================================
// Deben COINCIDIR con los tamaños del CSS (#escenario 600x450,
// #diana 100x100, #flecha alto 70). Si cambias uno, cambia el otro.
const ANCHO_ESCENARIO = 600;  // ancho total del tablero (px)
const ANCHO_DIANA     = 100;  // ancho de la diana (px)
const LARGO_FLECHA    = 70;   // alto de la flecha (px)
const ALTO_ESCENARIO  = 450;  // alto total del tablero (px)

// Altura (medida desde ABAJO) a la que está el CENTRO de la diana.
// Cálculo: alto 450 - (top 30 + radio 50) = 450 - 80 = 370.
const Y_IMPACTO = 370;

// ============================================================
// 3) VARIABLES DEL MODELO (estado del juego)
// ============================================================
let numFlechas;        // flechas elegidas al configurar
let flechasRestantes;  // flechas que quedan por disparar
let puntos = 0;        // puntos acumulados
let aciertos = 0;      // nº de tiros que dieron en la diana
let fallos = 0;        // nº de tiros fallados

let dianaX = 0;          // posición horizontal de la diana (equivale a su CSS left)
let dianaDireccion = 1;  // 1 = se mueve a la derecha, -1 = a la izquierda
let velocidad = 30;      // milisegundos entre movimientos (menos = más rápido)

let flechaY = 70;          // posición vertical de la flecha (equivale a su CSS bottom)
let flechaVolando = false; // true mientras la flecha está en el aire
let impactoComprobado = false; // evita comprobar el impacto más de una vez por tiro

let timerDiana;  // id del setInterval que mueve la diana
let timerFlecha; // id del setInterval que mueve la flecha

// ============================================================
// 4) ARRANQUE: estado inicial y EVENTOS
// ============================================================
// Al cargar: mostramos configuración y ocultamos el juego.
panelConfigurador.style.display = 'block';
panelJuego.style.display = 'none';

// addEventListener('click', funcion) = "cuando hagan clic, ejecuta esto".
// OJO: se pasa el NOMBRE de la función SIN paréntesis (no prepararJuego()).
btn_iniciar.addEventListener('click', prepararJuego);
btn_disparar.addEventListener('click', disparar);
btn_play.addEventListener('click', playDiana);
btn_stop.addEventListener('click', stopDiana);
btn_acelerar.addEventListener('click', acelerar);
btn_frenar.addEventListener('click', frenar);
btn_reiniciar.addEventListener('click', reiniciar);

// ============================================================
// 5) TEMPORIZADORES
// ============================================================
// setInterval(fn, ms) ejecuta fn CADA ms milisegundos, en bucle.
// Devuelve un id que sirve para pararlo con clearInterval(id).

function playDiana()
{
    // Si ya había un temporizador, lo quitamos para no tener DOS
    // moviendo la diana a la vez (iría el doble de rápido).
    clearInterval(timerDiana);
    timerDiana = setInterval(moverDiana, velocidad);
}

function stopDiana()
{
    clearInterval(timerDiana); // detiene el movimiento
}

function acelerar()
{
    // Acelerar = menos milisegundos entre movimientos.
    if (velocidad > 10)
        velocidad -= 5;
    else
        velocidad = 10; // tope mínimo (no puede ir más rápido que esto)

    playDiana(); // reiniciamos el timer con la nueva velocidad
}

function frenar()
{
    if (velocidad < 100)
        velocidad += 5;
    else
        velocidad = 100; // tope máximo (no puede ir más lento)

    playDiana();
}

// ============================================================
// 6) ANIMACIONES DEL JUEGO
// ============================================================

// --- Movimiento de la diana ---
function moverDiana()
{
    // Avanza 4 px en la dirección actual
    dianaX += 4 * dianaDireccion;

    // Rebote contra el borde DERECHO (no puede pasar de 600-100 = 500)
    if (dianaX >= ANCHO_ESCENARIO - ANCHO_DIANA)
    {
        dianaX = ANCHO_ESCENARIO - ANCHO_DIANA;
        dianaDireccion = -1; // ahora hacia la izquierda
    }
    // Rebote contra el borde IZQUIERDO
    if (dianaX <= 0)
    {
        dianaX = 0;
        dianaDireccion = 1; // ahora hacia la derecha
    }

    // Aplicamos el modelo a la vista moviendo el CSS left
    dianaDIV.style.left = dianaX + 'px';
}

// --- Disparar una flecha ---
function disparar()
{
    // No se puede disparar si ya hay una flecha volando o no quedan flechas
    if (flechaVolando || flechasRestantes <= 0)
        return;

    flechaVolando = true;      // bloquea nuevos disparos
    impactoComprobado = false; // este tiro aún no se ha comprobado
    flechasRestantes--;        // gastamos una flecha

    // Colocamos la flecha abajo y la hacemos visible
    flechaY = 70;
    flechaDIV.style.bottom = flechaY + 'px';
    flechaDIV.style.display = 'block';

    // Mientras vuela no se puede volver a disparar
    btn_disparar.disabled = true;

    // Reiniciar la animación del arco:
    // 1) quitamos la clase, 2) forzamos reflow, 3) la volvemos a poner.
    // Sin el truco del offsetWidth, si la clase ya estaba puesta no
    // se reiniciaría la animación.
    arcoDIV.classList.remove('disparo');
    void arcoDIV.offsetWidth; // <- "truco" para reiniciar la animación
    arcoDIV.classList.add('disparo');

    actualizarEstadisticas();

    // La flecha se mueve cada 10 ms
    timerFlecha = setInterval(volarFlecha, 10);
}

// --- Movimiento de la flecha (se llama cada 10ms) ---
function volarFlecha()
{
    flechaY += 8; // sube 8 px
    flechaDIV.style.bottom = flechaY + 'px';

    // La PUNTA de la flecha está en flechaY + LARGO_FLECHA (arriba).
    // Cuando la punta alcanza la altura del centro de la diana,
    // comprobamos si acierta. Solo una vez por tiro (impactoComprobado).
    if (!impactoComprobado && flechaY + LARGO_FLECHA >= Y_IMPACTO)
    {
        impactoComprobado = true;
        comprobarImpacto();
    }

    // Si se sale por arriba del escenario, termina el vuelo
    if (flechaY > ALTO_ESCENARIO)
    {
        terminarVuelo();
    }
}

// ============================================================
// 7) PUNTUACIÓN
// ============================================================
// La flecha siempre sube por el CENTRO horizontal (x = 300).
// Por eso solo importa la distancia horizontal entre el centro
// de la diana y el centro del escenario.
function comprobarImpacto()
{
    let centroDiana  = dianaX + ANCHO_DIANA / 2; // centro de la diana
    let centroFlecha = ANCHO_ESCENARIO / 2;      // 600/2 = 300
    let distancia    = Math.abs(centroDiana - centroFlecha);
    let puntosTiro   = 0;

    if (distancia <= 10)
        puntosTiro = 10;   // CENTRO rojo
    else if (distancia <= 25)
        puntosTiro = 5;    // Zona amarilla
    else if (distancia <= 50)
        puntosTiro = 1;    // Borde blanco (radio de la diana = 50)
    // Si distancia > 50 -> no toca la diana -> 0 puntos

    if (puntosTiro > 0)
    {
        // ACIERTO
        aciertos++;
        puntos += puntosTiro;
        document.getElementById('mensaje').innerText = '¡Diana! +' + puntosTiro;
        animarImpacto(puntosTiro);
        actualizarEstadisticas();
        terminarVuelo(); // se para el vuelo (en esta versión la flecha se oculta)
    }
    else
    {
        // FALLO: sumamos fallo, pero dejamos que la flecha siga
        // subiendo hasta salir de pantalla (terminarVuelo se llamará allí).
        fallos++;
        document.getElementById('mensaje').innerText = 'Has fallado...';
        actualizarEstadisticas();
    }
}

// --- Efectos visuales al acertar ---
function animarImpacto(puntosTiro)
{
    // Sacudida de la diana (mismo truco de reinicio que el arco)
    dianaDIV.classList.remove('impacto');
    void dianaDIV.offsetWidth;
    dianaDIV.classList.add('impacto');

    // Creamos un <span> flotante con "+puntos"
    let flotante = document.createElement('span');
    flotante.className = 'punto-flotante';
    flotante.innerText = '+' + puntosTiro;
    flotante.style.left = (dianaX + 35) + 'px'; // cerca de la diana
    flotante.style.top = '20px';
    escenario.appendChild(flotante); // lo metemos dentro del escenario

    // A los 1000 ms lo borramos del DOM (la animación dura 1s)
    setTimeout(function ()
    {
        flotante.remove();
    }, 1000);
}

// --- Fin del vuelo de una flecha ---
function terminarVuelo()
{
    clearInterval(timerFlecha);      // paramos el movimiento
    flechaDIV.style.display = 'none'; // ocultamos la flecha
    flechaVolando = false;           // ya se puede disparar otra vez

    if (flechasRestantes > 0)
    {
        btn_disparar.disabled = false; // quedan flechas: reactivamos botón
    }
    else
    {
        // No quedan flechas: fin de partida
        document.getElementById('mensaje').innerText =
            'Fin de la partida. Puntos: ' + puntos;
        btn_disparar.disabled = true;
    }
}

// ============================================================
// 8) ESTADÍSTICAS Y PREPARAR / REINICIAR
// ============================================================

// Copia los valores del MODELO a la VISTA (los <span>)
function actualizarEstadisticas()
{
    document.getElementById('n_flechas').innerText  = flechasRestantes;
    document.getElementById('n_puntos').innerText   = puntos;
    document.getElementById('n_aciertos').innerText = aciertos;
    document.getElementById('n_fallos').innerText   = fallos;
}

// Se ejecuta al pulsar "Iniciar Juego"
function prepararJuego()
{
    // Leemos los inputs. .value devuelve TEXTO, así que lo pasamos
    // a número entero con parseInt.
    numFlechas = parseInt(contenedorFlechas.value);
    let nivel  = parseInt(contenedorVelocidad.value);

    // Validación por si el usuario deja el campo vacío o pone algo raro
    if (isNaN(numFlechas) || numFlechas < 1)
        numFlechas = 5;
    if (isNaN(nivel) || nivel < 1 || nivel > 10)
        nivel = 5;

    // Convertimos el nivel (1-10) a milisegundos:
    // nivel 1 -> 110-10 = 100ms (lento)
    // nivel 10-> 110-100 = 10ms (rápido)
    velocidad = 110 - nivel * 10;

    // Reiniciamos TODAS las variables del modelo
    flechasRestantes = numFlechas;
    puntos = 0;
    aciertos = 0;
    fallos = 0;
    dianaX = 0;
    dianaDireccion = 1;
    flechaVolando = false;

    // Reiniciamos la vista
    flechaDIV.style.display = 'none';
    dianaDIV.style.left = '0px';
    btn_disparar.disabled = false;
    document.getElementById('mensaje').innerText = '¡Pulsa Disparar!';

    // Cambiamos de panel: ocultamos config y cabecera, mostramos juego
    panelConfigurador.style.display = 'none';
    cabecera.style.display = 'none';
    panelJuego.style.display = 'flex';

    actualizarEstadisticas();
    playDiana(); // arrancamos el movimiento de la diana
}

// Se ejecuta al pulsar "Reiniciar": vuelve a la configuración
function reiniciar()
{
    stopDiana();              // detiene la diana
    clearInterval(timerFlecha); // detiene la flecha (si volaba)

    panelJuego.style.display = 'none';
    panelConfigurador.style.display = 'block';
    cabecera.style.display = 'block';
}
