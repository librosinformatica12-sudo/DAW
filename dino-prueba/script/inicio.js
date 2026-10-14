document.addEventListener('DOMContentLoaded', () => {
    const escapar = (valor) => String(valor ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const salida = document.getElementById('resultado-carta');
    const seccionResultado = salida ? salida.closest('.resultado') : null;

    /* ---------- Plantillas de carta ---------- */

    const htmlCarta = (d) => {
        const stats = [
            ['HP', d.hp],
            ['Vigor', d.vigor],
            ['Ataque', d.ataque],
            ['Defensa', d.defensa],
            ['Agilidad', d.agilidad],
        ];
        const imagen = d.imagen_url
            ? `<img src="${escapar(d.imagen_url)}" alt="${escapar(d.especie)}">`
            : '<i class="bx bx-dna"></i>';

        return `
            <article class="carta">
                <figure class="carta-img">${imagen}</figure>
                <h3>${escapar(d.especie)}</h3>
                <p class="carta-especie">«${escapar(d.nombre)}»</p>
                <ul class="carta-stats">
                    ${stats.map(([etiqueta, valor]) => `
                        <li><b>${valor ?? '—'}</b><small>${etiqueta}</small></li>
                    `).join('')}
                </ul>
            </article>`;
    };

    const htmlCartaMini = (d) => {
        const imagen = d.imagen_url
            ? `<img class="fondo" src="${escapar(d.imagen_url)}" alt="" aria-hidden="true">
               <img src="${escapar(d.imagen_url)}" alt="${escapar(d.especie)}">`
            : '<i class="bx bx-dna" aria-hidden="true"></i>';
        const stats = [['HP', d.hp], ['ATQ', d.ataque], ['DEF', d.defensa], ['AGI', d.agilidad]];

        return `
            <li class="carta-mini">
                <div class="retrato">${imagen}</div>
                <h3>${escapar(d.especie)}</h3>
                <p class="apodo">«${escapar(d.nombre)}»</p>
                <div class="mini-stats">
                    ${stats.map(([etiqueta, valor]) => `
                        <span><b>${valor ?? '—'}</b><small>${etiqueta}</small></span>
                    `).join('')}
                </div>
            </li>`;
    };

    const mostrarCarta = (d) => {
        if (!salida) return;
        salida.innerHTML = htmlCarta(d);
        if (seccionResultado) seccionResultado.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    const mostrarCartas = (dinos) => {
        if (!salida) return;
        salida.innerHTML = `<ul class="cartas-grid">${dinos.map(htmlCartaMini).join('')}</ul>`;
        if (seccionResultado) seccionResultado.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    /* ---------- Cuenta atrás reutilizable para la espera ---------- */
    const cuentaAtras = (btn, segundos) => {
        let restante = Math.max(0, Math.round(segundos));
        btn.disabled = true;
        const pintar = () => {
            const m = Math.floor(restante / 60);
            const s = String(restante % 60).padStart(2, '0');
            btn.textContent = `En espera (${m}:${s})`;
        };
        pintar();
        const id = setInterval(() => {
            restante -= 1;
            if (restante <= 0) {
                clearInterval(id);
                btn.disabled = false;
                btn.textContent = 'Abrir sobre de prueba';
                return;
            }
            pintar();
        }, 1000);
    };

    /* ---------- Abrir sobre del día ---------- */
    const boton = document.getElementById('btn-sobre');
    const texto = document.getElementById('sobre-texto');

    if (boton && salida) {
        boton.addEventListener('click', async () => {
            boton.disabled = true;
            if (texto) {
                texto.textContent = 'Abriendo sobre...';
            }

            try {
                const respuesta = await fetch('php/cartaDiaria.php', { method: 'POST' });
                const datos = await respuesta.json();

                if (!datos.ok) {
                    if (texto) {
                        texto.textContent = datos.mensaje;
                    }
                    if (!/ya has abierto/i.test(datos.mensaje || '')) {
                        boton.disabled = false;
                    }
                    return;
                }

                mostrarCarta(datos.dinosaurio);
                if (texto) {
                    texto.textContent = '¡Sobre abierto! Vuelve mañana por otra carta.';
                }
            } catch (error) {
                if (texto) {
                    texto.textContent = 'No se pudo conectar con el servidor.';
                }
                boton.disabled = false;
            }
        });
    }

    /* ---------- Abrir sobre de prueba ---------- */
    const botonPrueba = document.querySelector('button[data-sobre="prueba"]');

    if (botonPrueba && salida) {
        const esperaInicial = parseInt(botonPrueba.dataset.espera || '0', 10);
        if (esperaInicial > 0) {
            cuentaAtras(botonPrueba, esperaInicial);
        }

        botonPrueba.addEventListener('click', async () => {
            botonPrueba.disabled = true;
            botonPrueba.textContent = 'Abriendo...';

            try {
                const respuesta = await fetch('php/abrirSobrePrueba.php', { method: 'POST' });
                const datos = await respuesta.json();

                if (!datos.ok) {
                    if (typeof datos.espera === 'number' && datos.espera > 0) {
                        cuentaAtras(botonPrueba, datos.espera);
                    } else {
                        botonPrueba.textContent = datos.mensaje;
                        botonPrueba.disabled = false;
                    }
                    return;
                }

                mostrarCartas(datos.dinosaurios);
                cuentaAtras(botonPrueba, datos.espera ?? 300);
            } catch (error) {
                botonPrueba.textContent = 'No se pudo conectar';
                botonPrueba.disabled = false;
            }
        });
    }
});
