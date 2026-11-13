/* =========================================================
   LÓGICA DEL JUEGO TIRO CON ARCO
   JavaScript conecta los botones con el HTML, mueve la diana,
   anima la flecha y calcula la puntuación.
   ========================================================= */

// VARIABLES DE VISTA: guardan referencias a elementos HTML para poder modificarlos.
// Variables Vista
const cabecera = document.getElementById('cabecera');
const panelConfigurador = document.getElementById('panel-config');
const panelJuego = document.getElementById('panel-juego');
const escenario = document.getElementById('escenario');
const dianaDIV = document.getElementById('diana');
const flechaDIV = document.getElementById('flecha');
const arcoDIV = document.getElementById('arco');
const contenedorFlechas = document.getElementById('spin_flechas');
const contenedorVelocidad = document.getElementById('spin_velocidad');
const btn_iniciar = document.getElementById('btn_iniciar_juego');
const btn_disparar = document.getElementById('btn_disparar');
const btn_play = document.getElementById('btn_play');
const btn_stop = document.getElementById('btn_stop');
const btn_acelerar = document.getElementById('btn_acelerar');
const btn_frenar = document.getElementById('btn_frenar');
const btn_reiniciar = document.getElementById('btn_reiniciar');

// CONSTANTES DEL ESCENARIO: medidas que deben coincidir con los tamaños del CSS.
//Constantes del escenario (en pixeles, tienen que coincidir con el CSS)
const ANCHO_ESCENARIO = 600;
const ANCHO_DIANA = 100;
const LARGO_FLECHA = 70;
const ALTO_ESCENARIO = 450;
const Y_IMPACTO = 370; //Altura (desde abajo) del centro de la diana

// VARIABLES DEL MODELO: estado y datos actuales de la partida.
//Variables Modelo
let numFlechas;
let flechasRestantes;
let puntos = 0;
let aciertos = 0;
let fallos = 0;

let dianaX = 0;        //Posicion de la diana (left)
let dianaDireccion = 1; //1 = derecha, -1 = izquierda
let velocidad = 30;    //(En milisegundos, cuanto menos mas rapido)
let flechaY = 70;      //Posicion de la flecha (bottom)
let flechaVolando = false;
let impactoComprobado = false;

let timerDiana;
let timerFlecha;

// PRIMER ARRANQUE: se muestra la configuración y se oculta el juego hasta iniciar.
//Primer arranque
panelConfigurador.style.display = 'block';
panelJuego.style.display = 'none';

btn_iniciar.addEventListener('click', prepararJuego);
btn_disparar.addEventListener('click', disparar);
btn_play.addEventListener('click', playDiana);
btn_stop.addEventListener('click', stopDiana);
btn_acelerar.addEventListener('click', acelerar);
btn_frenar.addEventListener('click', frenar);
btn_reiniciar.addEventListener('click', reiniciar);

// TEMPORIZADORES: setInterval repite una función cada cierto número de milisegundos.
// clearInterval detiene ese intervalo.
//TEMPORIZADORES
// Inicia el movimiento de la diana. Primero elimina un intervalo anterior para evitar duplicados.
function playDiana(){
    clearInterval(timerDiana); //Por si ya estaba en marcha, para que no haya dos a la vez
    timerDiana = setInterval(moverDiana, velocidad);
}
// Pausa el movimiento de la diana deteniendo su temporizador.
function stopDiana(){
    clearInterval(timerDiana);
}
// Acelera la diana reduciendo el intervalo; menos milisegundos significa más rapidez.
function acelerar(){
    if(velocidad > 10)
        velocidad -= 5;
    else
        velocidad = 10;
    playDiana();
}
// Frena la diana aumentando el intervalo; más milisegundos significa menos rapidez.
function frenar(){
    if(velocidad < 100)
        velocidad += 5;
    else
        velocidad = 100;
    playDiana();
}

// ANIMACIONES Y DISPARO: funciones que cambian posiciones y comprueban impactos.
//ANIMACIONES
// Mueve la diana cuatro píxeles por paso en la dirección actual y la hace rebotar en los bordes.
function moverDiana(){
    dianaX += 4 * dianaDireccion;
    //Rebote en los bordes
    if(dianaX >= ANCHO_ESCENARIO - ANCHO_DIANA){
        dianaX = ANCHO_ESCENARIO - ANCHO_DIANA;
        dianaDireccion = -1;
    }
    if(dianaX <= 0){
        dianaX = 0;
        dianaDireccion = 1;
    }
    dianaDIV.style.left = dianaX + 'px';
}

// Gestiona un disparo: comprueba que se pueda disparar, descuenta una flecha y lanza su temporizador.
function disparar(){
    if(flechaVolando || flechasRestantes <= 0)
        return;

    flechaVolando = true;
    impactoComprobado = false;
    flechasRestantes--;
    flechaY = 70;
    flechaDIV.style.bottom = flechaY + 'px';
    flechaDIV.style.display = 'block';
    btn_disparar.disabled = true;

    //Animacion del arco al disparar
    arcoDIV.classList.remove('disparo');
    void arcoDIV.offsetWidth; // Fuerza al navegador a recalcular el estilo para reiniciar la animación. //Truco para poder repetir la animacion
    arcoDIV.classList.add('disparo');

    actualizarEstadisticas();
    timerFlecha = setInterval(volarFlecha, 10);
}

// Avanza la flecha hacia arriba, comprueba una sola vez la altura de impacto y termina al salir del escenario.
function volarFlecha(){
    flechaY += 8;
    flechaDIV.style.bottom = flechaY + 'px';

    //Cuando la punta de la flecha llega a la altura de la diana, miramos si le da
    if(!impactoComprobado && flechaY + LARGO_FLECHA >= Y_IMPACTO){
        impactoComprobado = true;
        comprobarImpacto();
    }
    //Si la flecha se sale del escenario, termina el vuelo
    if(flechaY > ALTO_ESCENARIO){
        terminarVuelo();
    }
}

// Compara el centro de la diana con el centro fijo de la trayectoria de la flecha.
// La distancia decide si se consiguen 10, 5, 1 o 0 puntos.
function comprobarImpacto(){
    let centroDiana = dianaX + ANCHO_DIANA / 2;
    let centroFlecha = ANCHO_ESCENARIO / 2;
    let distancia = Math.abs(centroDiana - centroFlecha);
    let puntosTiro = 0;

    if(distancia <= 10)
        puntosTiro = 10;   //Centro
    else if(distancia <= 25)
        puntosTiro = 5;    //Zona amarilla
    else if(distancia <= 50)
        puntosTiro = 1;    //Borde

    if(puntosTiro > 0){
        aciertos++;
        puntos += puntosTiro;
        document.getElementById('mensaje').innerText = '¡Diana! +' + puntosTiro;
        animarImpacto(puntosTiro);
        //La flecha se queda en la diana, paramos el vuelo
        actualizarEstadisticas();
        terminarVuelo();
    }
    else{
        fallos++;
        document.getElementById('mensaje').innerText = 'Has fallado...';
        actualizarEstadisticas();
    }
}

// Añade la animación de la diana y crea un texto flotante con los puntos obtenidos.
function animarImpacto(puntosTiro){
    //Sacudida de la diana
    dianaDIV.classList.remove('impacto');
    void dianaDIV.offsetWidth;
    dianaDIV.classList.add('impacto');

    //Numerito que sube y desaparece
    let flotante = document.createElement('span');
    flotante.className = 'punto-flotante';
    flotante.innerText = '+' + puntosTiro;
    flotante.style.left = (dianaX + 35) + 'px';
    flotante.style.top = '20px';
    escenario.appendChild(flotante);
    setTimeout(function(){
        flotante.remove();
    }, 1000);
}

// Detiene el temporizador de la flecha, la oculta y habilita otro disparo si quedan flechas.
function terminarVuelo(){
    clearInterval(timerFlecha);
    flechaDIV.style.display = 'none';
    flechaVolando = false;

    if(flechasRestantes > 0){
        btn_disparar.disabled = false;
    }
    else{
        //Se acabaron las flechas
        document.getElementById('mensaje').innerText = 'Fin de la partida. Puntos: ' + puntos;
        btn_disparar.disabled = true;
    }
}

// Copia los valores actuales del modelo a los marcadores visibles del HTML.
function actualizarEstadisticas(){
    document.getElementById('n_flechas').innerText = flechasRestantes;
    document.getElementById('n_puntos').innerText = puntos;
    document.getElementById('n_aciertos').innerText = aciertos;
    document.getElementById('n_fallos').innerText = fallos;
}

// Lee la configuración, valida valores, reinicia contadores y muestra el panel de juego.
function prepararJuego(){
    // VARIABLES DEL MODELO: estado y datos actuales de la partida.
//Variables Modelo
    numFlechas = parseInt(contenedorFlechas.value);
    let nivel = parseInt(contenedorVelocidad.value);
    //Por si el usuario deja los campos vacios
    if(isNaN(numFlechas) || numFlechas < 1)
        numFlechas = 5;
    if(isNaN(nivel) || nivel < 1 || nivel > 10)
        nivel = 5;

    velocidad = 110 - nivel * 10; //Nivel 1 = 100ms (lento), nivel 10 = 10ms (rapido)
    flechasRestantes = numFlechas;
    puntos = 0;
    aciertos = 0;
    fallos = 0;
    dianaX = 0;
    dianaDireccion = 1;
    flechaVolando = false;

    flechaDIV.style.display = 'none';
    dianaDIV.style.left = '0px';
    btn_disparar.disabled = false;
    document.getElementById('mensaje').innerText = '¡Pulsa Disparar!';

    panelConfigurador.style.display = 'none';
    cabecera.style.display = 'none';
    panelJuego.style.display = 'flex';

    actualizarEstadisticas();
    playDiana();
}

// Detiene los temporizadores y vuelve a la pantalla inicial de configuración.
function reiniciar(){
    stopDiana();
    clearInterval(timerFlecha);
    panelJuego.style.display = 'none';
    panelConfigurador.style.display = 'block';
    cabecera.style.display = 'block';
}
