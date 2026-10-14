/**
 * coleccion.js
 * Carga la colección por fetch, permite buscar y ordenar las cartas.
 */
import { pedir, crearCarta } from './main.js';

const lista = document.getElementById('lista-coleccion');
const vacio = document.getElementById('vacio');
const buscador = document.getElementById('buscador');
const orden = document.getElementById('orden');
const barra = document.getElementById('barra-interior');
const textoProgreso = document.getElementById('texto-progreso');

let cartas = [];

/** Descarga la colección del usuario. */
async function cargar() {
    try {
        const datos = await pedir('api/coleccion.php');
        cartas = datos.cartas;

        const { resumen } = datos;
        barra.style.width = resumen.porcentaje + '%';
        textoProgreso.textContent =
            `${resumen.especies} de ${resumen.catalogo} especies (${resumen.porcentaje}%) · ${resumen.cartas} cartas`;

        pintar();
    } catch (error) {
        textoProgreso.textContent = error.message;
    }
}

/** Aplica búsqueda y orden y dibuja las cartas. */
function pintar() {
    const texto = buscador.value.trim().toLowerCase();

    const visibles = cartas.filter((d) =>
        d.nombre.toLowerCase().includes(texto) ||
        d.especie.toLowerCase().includes(texto)
    );

    if (orden.value === 'nombre') {
        visibles.sort((a, b) => a.nombre.localeCompare(b.nombre));
    } else if (orden.value === 'copias') {
        visibles.sort((a, b) => b.copias - a.copias);
    }

    lista.innerHTML = '';
    visibles.forEach((dino) => lista.appendChild(crearCarta(dino, { copias: dino.copias })));
    vacio.hidden = visibles.length > 0;
}

buscador.addEventListener('input', pintar);
orden.addEventListener('change', pintar);

cargar();
