/**
 * dinopedia.js
 * Muestra el catálogo completo y permite añadir cartas a la colección.
 */
import { pedir, crearCarta } from './main.js';

const lista = document.getElementById('lista-dinopedia');
const buscador = document.getElementById('buscador');
const filtroPeriodo = document.getElementById('filtro-periodo');

let dinosaurios = [];

/** Descarga el catálogo completo. */
async function cargar() {
    try {
        const datos = await pedir('api/dinosaurios.php');
        dinosaurios = datos.dinosaurios;
        rellenarPeriodos();
        pintar();
    } catch (error) {
        lista.innerHTML = `<li class="vacio">${error.message}</li>`;
    }
}

/** Rellena el desplegable de periodos sin repetir. */
function rellenarPeriodos() {
    const periodos = [...new Set(dinosaurios.map((d) => d.periodo))].sort();
    periodos.forEach((periodo) => {
        const opcion = document.createElement('option');
        opcion.value = periodo;
        opcion.textContent = periodo;
        filtroPeriodo.appendChild(opcion);
    });
}

/** Aplica búsqueda y filtro y dibuja las cartas. */
function pintar() {
    const texto = buscador.value.trim().toLowerCase();
    const periodo = filtroPeriodo.value;

    const visibles = dinosaurios.filter((d) => {
        const coincide =
            d.nombre.toLowerCase().includes(texto) ||
            d.especie.toLowerCase().includes(texto);
        return coincide && (!periodo || d.periodo === periodo);
    });

    lista.innerHTML = '';
    visibles.forEach((dino) => {
        const carta = crearCarta(dino);

        const boton = document.createElement('button');
        boton.className = 'boton pequeno';
        boton.textContent = 'Añadir';
        boton.addEventListener('click', () => anadir(dino, boton));

        carta.appendChild(boton);
        lista.appendChild(carta);
    });
}

/** Envía la petición para añadir una carta. */
async function anadir(dino, boton) {
    boton.disabled = true;
    boton.textContent = 'Añadiendo…';

    const cuerpo = new FormData();
    cuerpo.append('dinosaurio_id', dino.id);

    try {
        await pedir('api/anadir_carta.php', { method: 'POST', body: cuerpo });
        boton.textContent = '¡Añadido!';
        boton.classList.add('ok');
    } catch (error) {
        boton.textContent = error.message;
        boton.disabled = false;
        setTimeout(() => { boton.textContent = 'Añadir'; }, 2000);
    }
}

buscador.addEventListener('input', pintar);
filtroPeriodo.addEventListener('change', pintar);

cargar();
