(() => {
    'use strict';

    const config = window.DINOCARDS_INTERCAMBIO;
    if (!config) return;

    const selector = document.getElementById('explorador');
    const misCartasEl = document.getElementById('mis-cartas');
    const susCartasEl = document.getElementById('sus-cartas');
    const btnCargar = document.getElementById('cargar-cartas');
    const btnTrade = document.getElementById('realizar-intercambio');
    const feedback = document.getElementById('feedback-intercambio');
    const resumen = document.getElementById('resumen-intercambio');

    let misCartas = [];
    let susCartas = [];
    const seleccionMia = new Set();
    const seleccionSuya = new Set();

    function escapar(valor) {
        return String(valor ?? '').replace(/[&<>"']/g, caracter => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[caracter]);
    }

    function mostrarMensaje(texto, tipo = '') {
        feedback.textContent = texto;
        feedback.className = `intercambio-feedback ${tipo}`;
    }

    async function pedir(url, opciones = {}) {
        const respuesta = await fetch(url, {
            credentials: 'same-origin',
            ...opciones
        });
        let datos;
        try {
            datos = await respuesta.json();
        } catch {
            throw new Error('El servidor devolvió una respuesta no válida.');
        }
        if (!respuesta.ok || !datos.ok) {
            throw new Error(datos.mensaje || 'No se pudo completar la petición.');
        }
        return datos;
    }

    function renderCartas(cartas, contenedor, seleccion) {
        if (!cartas.length) {
            contenedor.innerHTML = '<p class="intercambio-vacio">Este coleccionista todavía no tiene cartas.</p>';
            return;
        }

        contenedor.innerHTML = cartas.map(carta => {
            const id = Number(carta.coleccion_id);
            const activa = seleccion.has(id);
            const nombre = escapar(carta.nombre || `Carta #${id}`);
            const especie = escapar(carta.especie || 'Especie desconocida');
            const nombreImagen = String(carta.image_url || '').split(/[\\/]/).pop();
            const imagen = nombreImagen
                ? `<img src="${config.imagenes}${encodeURIComponent(nombreImagen)}" alt="${nombre}" loading="lazy">`
                : '<span class="intercambio-placeholder">D</span>';

            return `<button type="button" class="intercambio-carta${activa ? ' seleccionada' : ''}"
                        data-id="${id}" aria-pressed="${activa}">
                <span class="intercambio-numero">#${id}</span>
                ${imagen}
                <strong>${nombre}</strong>
                <small>${especie}</small>
                <span class="intercambio-seleccion">${activa ? '✓ Seleccionada' : '+ Seleccionar'}</span>
            </button>`;
        }).join('');

        contenedor.querySelectorAll('.intercambio-carta').forEach(boton => {
            boton.addEventListener('click', () => {
                const id = Number(boton.dataset.id);
                if (seleccion.has(id)) seleccion.delete(id);
                else seleccion.add(id);
                renderCartas(cartas, contenedor, seleccion);
                actualizarResumen();
            });
        });
    }

    function actualizarResumen() {
        document.getElementById('contador-mias').textContent = `${seleccionMia.size} seleccionadas`;
        document.getElementById('contador-suyas').textContent = `${seleccionSuya.size} seleccionadas`;

        const cantidadesIguales = seleccionMia.size > 0 && seleccionMia.size === seleccionSuya.size;
        btnTrade.disabled = !cantidadesIguales || !selector.value;

        resumen.textContent = cantidadesIguales
            ? `Ofreces ${seleccionMia.size} carta(s) y recibirás ${seleccionSuya.size}.`
            : 'Selecciona la misma cantidad de cartas en ambos lados.';
    }

    async function cargarUsuarios() {
        selector.innerHTML = '<option value="">Cargando coleccionistas...</option>';
        try {
            const datos = await pedir(`${config.endpoint}?ajax=usuarios`);
            selector.innerHTML = '<option value="">Elige un coleccionista...</option>' +
                datos.usuarios.map(usuario =>
                    `<option value="${Number(usuario.id)}">${escapar(usuario.username)}</option>`
                ).join('');
            if (!datos.usuarios.length) {
                selector.innerHTML = '<option value="">No hay otros coleccionistas</option>';
            }
        } catch (error) {
            selector.innerHTML = '<option value="">No se pudieron cargar</option>';
            mostrarMensaje(error.message, 'error');
        }
    }

    async function cargarCartas() {
        seleccionMia.clear();
        seleccionSuya.clear();
        misCartas = [];
        susCartas = [];
        misCartasEl.innerHTML = '<p class="intercambio-vacio">Cargando tus cartas...</p>';
        susCartasEl.innerHTML = '<p class="intercambio-vacio">Cargando sus cartas...</p>';
        actualizarResumen();

        if (!selector.value) {
            mostrarMensaje('Selecciona primero a otro coleccionista.', 'error');
            return;
        }

        btnCargar.disabled = true;
        try {
            const datos = await pedir(
                `${config.endpoint}?ajax=cartas&usuario=${encodeURIComponent(selector.value)}`
            );
            misCartas = datos.misCartas;
            susCartas = datos.susCartas;
            document.getElementById('titulo-sus-cartas').textContent = `Cartas de ${datos.otroUsuario}`;
            renderCartas(misCartas, misCartasEl, seleccionMia);
            renderCartas(susCartas, susCartasEl, seleccionSuya);
            mostrarMensaje('');
            actualizarResumen();
        } catch (error) {
            misCartasEl.innerHTML = '<p class="intercambio-vacio">No se pudieron cargar tus cartas.</p>';
            susCartasEl.innerHTML = '<p class="intercambio-vacio">No se pudieron cargar sus cartas.</p>';
            mostrarMensaje(error.message, 'error');
        } finally {
            btnCargar.disabled = false;
        }
    }

    btnCargar.addEventListener('click', cargarCartas);

    selector.addEventListener('change', () => {
        misCartas = [];
        susCartas = [];
        seleccionMia.clear();
        seleccionSuya.clear();
        misCartasEl.innerHTML = '<p class="intercambio-vacio">Pulsa «Ver sus cartas».</p>';
        susCartasEl.innerHTML = '<p class="intercambio-vacio">Las cartas aparecerán aquí.</p>';
        document.getElementById('titulo-sus-cartas').textContent = 'Sus cartas';
        actualizarResumen();
        mostrarMensaje('');
    });

    btnTrade.addEventListener('click', async () => {
        if (btnTrade.disabled) return;

        btnTrade.disabled = true;
        mostrarMensaje('Procesando el intercambio...', '');
        try {
            const datos = await pedir(`${config.endpoint}?ajax=intercambiar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': config.csrf
                },
                body: JSON.stringify({
                    usuario: Number(selector.value),
                    mis_cartas: [...seleccionMia],
                    sus_cartas: [...seleccionSuya]
                })
            });
            mostrarMensaje(datos.mensaje, 'success');
            await cargarCartas();
        } catch (error) {
            mostrarMensaje(error.message, 'error');
        } finally {
            actualizarResumen();
        }
    });

    cargarUsuarios();
})();
