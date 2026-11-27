/**
 * ============================================================
 * intercambio.js - Lógica de la interfaz de intercambios DinoCards
 * ============================================================
 */

// Función global para mantener compatibilidad con oninput="filtrarColeccion()" en el HTML
window.filtrarColeccion = function() {
    const input = document.getElementById('buscador');
    const filtro = input ? input.value.toLowerCase().trim() : '';
    const cartas = document.querySelectorAll('.carta-mini');
    const sinResultados = document.getElementById('sin-resultados');
    let visibles = 0;

    cartas.forEach(carta => {
        const textoBuscar = carta.getAttribute('data-buscar') || '';
        if (textoBuscar.includes(filtro)) {
            carta.removeAttribute('hidden');
            visibles++;
        } else {
            carta.setAttribute('hidden', 'true');
        }
    });

    if (sinResultados) {
        sinResultados.hidden = visibles > 0;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // --- 1. GESTIÓN DEL PANEL DE INTERCAMBIO (USUARIO 1) ---
    const cartasGrid = document.getElementById('cartas-grid');
    const panelUsuario1Cards = document.querySelector('[data-cards="1"]');
    const aceptarUsuario1 = document.getElementById('aceptar-usuario1');
    const aceptarUsuario2 = document.getElementById('aceptar-usuario2');
    const estadoIntercambio = document.getElementById('estado-intercambio');

    // Conjunto para almacenar los IDs de las cartas seleccionadas y evitar duplicados
    const cartasSeleccionadas = new Set();

    function actualizarContador(panelNum, cantidad) {
        const contador = document.querySelector(`[data-count="${panelNum}"]`);
        if (contador) {
            contador.textContent = `${cantidad} ${cantidad === 1 ? 'carta' : 'cartas'}`;
        }
    }

    function actualizarEstadoIntercambio() {
        if (!estadoIntercambio) return;
        const aceptado1 = aceptarUsuario1 ? aceptarUsuario1.checked : false;
        const aceptado2 = aceptarUsuario2 ? aceptarUsuario2.checked : false;
        const total1 = cartasSeleccionadas.size;

        let texto = 'Selecciona tus cartas para preparar la oferta.';
        let icono = 'bx-info-circle';

        if (total1 > 0) {
            texto = 'Oferta en preparación.';
        }
        if (aceptado1 && aceptado2) {
            texto = '¡Intercambio aceptado por ambos usuarios! Listo para completar.';
            icono = 'bx-check-shield';
        } else if (aceptado1) {
            texto = 'Has aceptado el intercambio. Esperando al otro usuario.';
            icono = 'bx-time-five';
        } else if (aceptado2) {
            texto = 'El otro usuario ha aceptado. Revisa tu oferta.';
            icono = 'bx-error-circle';
        }

        estadoIntercambio.innerHTML = `<i class="bx ${icono}" aria-hidden="true"></i><span>${texto}</span>`;
    }

    // Añadir carta desde la colección propia al panel de intercambio (Usuario 1)
    if (cartasGrid) {
        cartasGrid.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-trade-card]');
            if (!btn) return;

            const cardId = btn.getAttribute('data-id');
            if (cartasSeleccionadas.has(cardId)) {
                // Evitar añadir la misma carta varias veces al mismo panel
                return;
            }

            const cardData = {
                id: cardId,
                nombre: btn.getAttribute('data-nombre'),
                especie: btn.getAttribute('data-especie'),
                imagen: btn.getAttribute('data-imagen')
            };

            cartasSeleccionadas.add(cardId);
            renderizarCartaEnPanel(cardData, 1);
            actualizarContador(1, cartasSeleccionadas.size);

            // IMPORTANTE: Si se modifica el contenido del intercambio después de aceptar,
            // se cancela automáticamente la aceptación para evitar aceptar una propuesta diferente.
            if (aceptarUsuario1 && aceptarUsuario1.checked) {
                aceptarUsuario1.checked = false;
            }

            actualizarEstadoIntercambio();
        });
    }

    function renderizarCartaEnPanel(cardData, panelNum) {
        const container = document.querySelector(`[data-cards="${panelNum}"]`);
        if (!container) return;

        // Quitar mensaje de panel vacío si existe
        const vazio = container.querySelector('.panel-vacio');
        if (vazio) vazio.remove();

        const div = document.createElement('div');
        div.className = 'carta-oferta';
        div.setAttribute('data-card-id', cardData.id);

        const imgHtml = cardData.imagen 
            ? `<img src="${escapeHtml(cardData.imagen)}" alt="${escapeHtml(cardData.nombre)}">`
            : `<div class="oferta-sin-imagen"><i class='bx bx-dna' aria-hidden="true"></i></div>`;

        div.innerHTML = `
            ${imgHtml}
            <strong>${escapeHtml(cardData.nombre)}</strong>
            <small>${escapeHtml(cardData.especie)}</small>
            ${panelNum === 1 ? `<button type="button" class="quitar-carta" data-remove-id="${cardData.id}" aria-label="Quitar carta"><i class="bx bx-x" aria-hidden="true"></i></button>` : ''}
        `;

        container.appendChild(div);
    }

    // Quitar carta del panel de intercambio (Usuario 1)
    if (panelUsuario1Cards) {
        panelUsuario1Cards.addEventListener('click', (e) => {
            const btnQuitar = e.target.closest('.quitar-carta');
            if (!btnQuitar) return;

            const cardId = btnQuitar.getAttribute('data-remove-id');
            cartasSeleccionadas.delete(cardId);

            const cardElement = btnQuitar.closest('.carta-oferta');
            if (cardElement) cardElement.remove();

            actualizarContador(1, cartasSeleccionadas.size);

            if (cartasSeleccionadas.size === 0) {
                panelUsuario1Cards.innerHTML = `<p class="panel-vacio">Tus cartas seleccionadas aparecerán aquí.</p>`;
            }

            // Cancelar aceptación automáticamente si se modifica la oferta
            if (aceptarUsuario1 && aceptarUsuario1.checked) {
                aceptarUsuario1.checked = false;
            }

            actualizarEstadoIntercambio();
        });
    }

    // --- 2. SISTEMA DE ACEPTACIÓN ---
    if (aceptarUsuario1) {
        aceptarUsuario1.addEventListener('change', actualizarEstadoIntercambio);
    }
    if (aceptarUsuario2) {
        aceptarUsuario2.addEventListener('change', actualizarEstadoIntercambio);
    }

    // --- 3. CHAT VISUAL DE DEMOSTRACIÓN ---
    const formularioChat = document.getElementById('formulario-chat');
    const mensajeInput = document.getElementById('mensaje-input');
    const mensajesChat = document.getElementById('mensajes-chat');
    const chatVacio = document.getElementById('chat-vacio');

    if (formularioChat && mensajeInput && mensajesChat) {
        formularioChat.addEventListener('submit', (e) => {
            e.preventDefault();
            const texto = mensajeInput.value.trim();
            if (!texto) return;

            if (chatVacio) {
                chatVacio.remove();
            }

            const msgDiv = document.createElement('div');
            msgDiv.className = 'mensaje-chat';
            const hora = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            msgDiv.innerHTML = `
                ${escapeHtml(texto)}
                <time>${hora}</time>
            `;

            mensajesChat.appendChild(msgDiv);
            mensajesChat.scrollTop = mensajesChat.scrollHeight;
            mensajeInput.value = '';
            mensajeInput.focus();
        });
    }

    // Función auxiliar para prevenir inyección XSS en textos renderizados por JS
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>'"]/g, 
            tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
        );
    }
});