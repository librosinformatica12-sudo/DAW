/**
 * inicio.js
 * Panel principal: resumen de la colección y apertura de sobres.
 */
import { pedir, crearCarta } from './main.js';

const btnDiario = document.getElementById('btn-diario');
const btnPrueba = document.getElementById('btn-prueba');
const resultado = document.getElementById('resultado');
const resultadoTitulo = document.getElementById('resultado-titulo');
const listaResultado = document.getElementById('lista-resultado');

/** Trae el resumen y lo pinta en las tarjetas de estadísticas. */
async function cargarResumen() {
    try {
        const { resumen } = await pedir('api/coleccion.php');
        document.getElementById('r-cartas').textContent = resumen.cartas;
        document.getElementById('r-especies').textContent = resumen.especies;
        document.getElementById('r-porcentaje').textContent = resumen.porcentaje + '%';
    } catch {
        // Si falla el resumen no bloqueamos la página
    }
}

/** Muestra las cartas obtenidas en la sección de resultado. */
function mostrarResultado(titulo, dinosaurios) {
    resultadoTitulo.textContent = titulo;
    listaResultado.innerHTML = '';
    dinosaurios.forEach((dino) => listaResultado.appendChild(crearCarta(dino)));

    resultado.hidden = false;
    resultado.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Sobre del día
btnDiario?.addEventListener('click', async () => {
    const estado = document.getElementById('estado-diario');
    estado.textContent = '';
    btnDiario.disabled = true;

    try {
        const datos = await pedir('api/carta_diaria.php', { method: 'POST', body: new FormData() });
        mostrarResultado('Sobre del día', [datos.dinosaurio]);
        estado.textContent = datos.mensaje;
        cargarResumen();
    } catch (error) {
        estado.textContent = error.message;
        btnDiario.disabled = false;
    }
});

// Sobre de prueba
btnPrueba?.addEventListener('click', async () => {
    const estado = document.getElementById('estado-prueba');
    estado.textContent = 'Abriendo sobre…';
    btnPrueba.disabled = true;

    try {
        const datos = await pedir('api/abrir_prueba.php', { method: 'POST', body: new FormData() });
        mostrarResultado('Sobre de prueba', datos.dinosaurios);
        estado.textContent = datos.mensaje;
        cargarResumen();
    } catch (error) {
        estado.textContent = error.message;
    } finally {
        btnPrueba.disabled = false;
    }
});

cargarResumen();
