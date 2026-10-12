document.addEventListener('DOMContentLoaded', () => {
    /* ---------- Carrusel de sobres ---------- */
    const carrusel = document.getElementById('carrusel');
    const pista = document.getElementById('carrusel-pista');
    const botonPrev = document.getElementById('sobre-prev');
    const botonNext = document.getElementById('sobre-next');
    const puntosCont = document.getElementById('carrusel-puntos');
    const contador = document.getElementById('sobres-contador');

    if (carrusel && pista) {
        const slides = Array.from(pista.children);
        let indice = slides.findIndex((slide) => slide.classList.contains('activo'));
        if (indice < 0) {
            indice = 0;
        }

        const puntos = slides.map((_, i) => {
            if (!puntosCont) {
                return null;
            }
            const punto = document.createElement('button');
            punto.type = 'button';
            punto.setAttribute('aria-label', `Ir al sobre ${i + 1}`);
            punto.addEventListener('click', () => irA(i));
            puntosCont.appendChild(punto);
            return punto;
        });

        const irA = (n) => {
            indice = (n + slides.length) % slides.length;
            slides.forEach((slide, i) => slide.classList.toggle('activo', i === indice));
            puntos.forEach((punto, i) => {
                if (punto) {
                    punto.classList.toggle('activo', i === indice);
                }
            });
            if (contador) {
                contador.textContent = `${indice + 1} / ${slides.length}`;
            }
        };

        if (botonPrev) {
            botonPrev.addEventListener('click', () => irA(indice - 1));
        }
        if (botonNext) {
            botonNext.addEventListener('click', () => irA(indice + 1));
        }
        carrusel.addEventListener('keydown', (evento) => {
            if (evento.key === 'ArrowLeft') { irA(indice - 1); }
            if (evento.key === 'ArrowRight') { irA(indice + 1); }
        });

        irA(indice);
    }

    /* ---------- Abrir sobre del día ---------- */
    const boton = document.getElementById('btn-sobre');
    const salida = document.getElementById('resultado-carta');
    const texto = document.getElementById('sobre-texto');
    const seccionResultado = salida ? salida.closest('.resultado') : null;

    if (!boton || !salida) {
        return;
    }

    const escapar = (valor) => String(valor ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const mostrarCarta = (d) => {
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

        salida.innerHTML = `
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

        if (seccionResultado) {
            seccionResultado.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    };

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
});
