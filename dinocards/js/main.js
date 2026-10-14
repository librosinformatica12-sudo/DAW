/**
 * main.js
 * Funciones comunes a todas las páginas (se importa como módulo ES6).
 */

/** Escapa un texto para insertarlo en HTML sin peligro. */
export function escapar(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}

/** Petición fetch que devuelve JSON y lanza error si la respuesta falla. */
export async function pedir(url, opciones = {}) {
    const respuesta = await fetch(url, {
        credentials: 'same-origin',
        ...opciones,
    });

    const datos = await respuesta.json();
    if (!respuesta.ok) {
        throw new Error(datos.mensaje || 'Error de conexión con el servidor.');
    }
    return datos;
}

/** Crea el elemento <li> de una carta a partir de un dinosaurio. */
export function crearCarta(dino, { copias = 0 } = {}) {
    const li = document.createElement('li');
    li.className = 'carta';
    li.dataset.id = dino.id;

    li.innerHTML = `
        <div class="carta-imagen">
            <img src="${escapar(dino.imagen_url)}" alt="${escapar(dino.nombre)}" loading="lazy">
            ${copias > 1 ? `<span class="copias">x${copias}</span>` : ''}
        </div>
        <div class="carta-info">
            <h3>${escapar(dino.nombre)}</h3>
            <p class="especie">${escapar(dino.especie)}</p>
            <p class="periodo">${escapar(dino.periodo)}</p>
            <ul class="stats">
                <li><span>HP</span> ${dino.hp}</li>
                <li><span>ATQ</span> ${dino.ataque}</li>
                <li><span>DEF</span> ${dino.defensa}</li>
                <li><span>AGI</span> ${dino.agilidad}</li>
            </ul>
        </div>
    `;

    return li;
}

/** Activa los botones "Mostrar/Ocultar" de los campos de contraseña. */
export function activarVerClave() {
    document.querySelectorAll('.ver-clave').forEach((boton) => {
        boton.addEventListener('click', () => {
            const campo = document.getElementById(boton.dataset.para);
            if (!campo) return;

            const oculta = campo.type === 'password';
            campo.type = oculta ? 'text' : 'password';
            boton.textContent = oculta ? 'Ocultar' : 'Mostrar';
        });
    });
}
