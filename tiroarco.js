// Variables Vista
const formularioTiro = document.getElementById('formulario-tiro');
const entradaVertical = document.getElementById('coordenada-vertical');
const entradaHorizontal = document.getElementById('coordenada-horizontal');
const entradaPotencia = document.getElementById('potencia');
const textoViento = document.getElementById('informacion-viento');
const componentesViento = document.getElementById('componentes-viento');
const textoLanzamiento = document.getElementById('numero-lanzamiento');
const textoPuntuacion = document.getElementById('puntuacion');
const textoResultado = document.getElementById('resultado-tiro');
const mensajeFinal = document.getElementById('mensaje-final');
const historialTiros = document.getElementById('historial-tiros');
const marcadorImpacto = document.getElementById('impacto');
const botonLanzar = document.getElementById('btn-lanzar');
const botonReiniciar = document.getElementById('btn-reiniciar');


const valorPotencia = document.getElementById('valor-potencia');


// Variables Modelo
let numeroLanzamiento = 1;
let puntuacionTotal = 0;
let vientoVertical = 0;
let vientoHorizontal = 0;

formularioTiro.addEventListener('submit', lanzarFlecha);
botonReiniciar.addEventListener('click', reiniciarPartida);



entradaPotencia.addEventListener('input', actualizarValorPotencia);

actualizarValorPotencia();
cambiarViento();

function actualizarValorPotencia()
{
    valorPotencia.innerText = entradaPotencia.value;
}

function cambiarViento()
{
    vientoVertical = Math.floor(Math.random() * 11) - 5;
    vientoHorizontal = Math.floor(Math.random() * 11) - 5;

    const fuerza = Math.round(Math.sqrt(
        vientoVertical * vientoVertical + vientoHorizontal * vientoHorizontal
    ));

    textoViento.innerText = 'Fuerza: ' + fuerza + ' · Dirección: ' + calcularDireccion();
    componentesViento.innerText = 'Componente vertical: ' + describirComponente(vientoVertical, 'arriba', 'abajo')
        + ' · Componente horizontal: ' + describirComponente(vientoHorizontal, 'derecha', 'izquierda');
}

function describirComponente(componente, positivo, negativo)
{
    if (componente > 0) {
        return '+' + componente + ' (' + positivo + ')';
    }
    if (componente < 0) {
        return componente + ' (' + negativo + ')';
    }
    return '0 (sin viento)';
}

function calcularDireccion()
{
    if (vientoVertical === 0 && vientoHorizontal === 0) {
        return 'calma';
    }
    if (vientoVertical === 0) {
        return vientoHorizontal > 0 ? 'derecha' : 'izquierda';
    }
    if (vientoHorizontal === 0) {
        return vientoVertical > 0 ? 'arriba' : 'abajo';
    }

    let direccion = vientoVertical > 0 ? 'arriba' : 'abajo';
    direccion += vientoHorizontal > 0 ? ' y a la derecha' : ' y a la izquierda';
    return direccion;
}

function lanzarFlecha(evento)
{
    evento.preventDefault();

    const vertical = Number(entradaVertical.value);
    const horizontal = Number(entradaHorizontal.value);
    const potencia = Number(entradaPotencia.value);

    if (!esValorValido(vertical) || !esValorValido(horizontal) || !esValorValido(potencia)) {
        mensajeFinal.innerText = 'Introduce valores del 1 al 100 en los tres campos.';
        return;
    }

    const factorViento = (101 - potencia) / 20;
    const impactoVertical = vertical + vientoVertical * factorViento;
    const impactoHorizontal = horizontal + vientoHorizontal * factorViento;
    const puntos = calcularPuntuacion(impactoVertical, impactoHorizontal);

    puntuacionTotal += puntos;
    textoPuntuacion.innerText = puntuacionTotal;
    textoResultado.innerText = crearTextoResultado(
        impactoVertical,
        impactoHorizontal,
        puntos
    );
    mostrarImpacto(impactoVertical, impactoHorizontal);
    anadirAlHistorial(vertical, horizontal, potencia, impactoVertical, impactoHorizontal, puntos);

    if (numeroLanzamiento === 10) {
        terminarPartida();
        return;
    }

    numeroLanzamiento++;
    textoLanzamiento.innerText = numeroLanzamiento;
    mensajeFinal.innerText = '';
    cambiarViento();
}

function esValorValido(valor)
{
    /*EJERCICIO 3 - Devuelve true si el valor está entre 1 y 100*/
    return valor >= 1 && valor <= 100;
}

function calcularPuntuacion(vertical, horizontal)
{
    if (vertical < 1 || vertical > 100 || horizontal < 1 || horizontal > 100) {
        return 0;
    }

    const diferenciaVertical = vertical - 50;
    const diferenciaHorizontal = horizontal - 50;
    const distancia = Math.sqrt(
        diferenciaVertical * diferenciaVertical + diferenciaHorizontal * diferenciaHorizontal
    );

    if (distancia > 50) {
        return 0;
    }
    return Math.min(10, 11 - Math.ceil(distancia / 5));
}

function crearTextoResultado(vertical, horizontal, puntos)
{
    if (puntos === 0) {
        return 'La flecha queda fuera de la diana. 0 puntos.';
    }
    return 'Impacto: vertical ' + vertical.toFixed(1) + ', horizontal '
        + horizontal.toFixed(1) + '. Puntuación: ' + puntos + '.';
}

function mostrarImpacto(vertical, horizontal)
{
    if (calcularPuntuacion(vertical, horizontal) === 0) {
        marcadorImpacto.setAttribute('visibility', 'hidden');
        return;
    }

    marcadorImpacto.setAttribute('cx', horizontal);
    marcadorImpacto.setAttribute('cy', 100 - vertical);
    marcadorImpacto.setAttribute('visibility', 'visible');
}

function anadirAlHistorial(vertical, horizontal, potencia, impactoVertical, impactoHorizontal, puntos)
{
    const registro = document.createElement('li');
    let texto;
    //PREGUNTA 4 - Elabora la construcción de los elementos de la lista que informan que numerolanzamiento
    //           - se lanzó, hacia qué componente vertical y horizontal se apuntó, que potencia se utilizó
    //           - y qué puntos se sacaron.

    //texto = ........

    registro.innerText = texto;
    historialTiros.appendChild(registro);
}

function terminarPartida()
{
    textoResultado.innerText += ' Partida terminada.';
    mensajeFinal.innerText = '¡Partida terminada! Puntuación final: ' + puntuacionTotal + ' puntos.';
    botonLanzar.disabled = true;
    botonReiniciar.hidden = false;
    entradaVertical.disabled = true;
    entradaHorizontal.disabled = true;
    entradaPotencia.disabled = true;
}

function reiniciarPartida()
{
    //PREGUNTA 5 - Elabora la función de reinicio de la partida que se acciona al pulsar el botonReiniciar
    //              Deja todo listo para volver a jugar con todo lo necesario habilitado e inicializado.
    //              Es decir, primer estado de las variables tanto del modelo como de la vista.
    //              Por ejemplo: volver al numeroLanzamiento 1, reiniciar a 0 la puntuacion,
    //              colocar los textos como estaban al principio, habilitar inputs otra vez.
    
    //???? = ????
    //???? = ????
    //???? = ????
    //???? = ????
    //???? = ????
    //........



    cambiarViento();
}
