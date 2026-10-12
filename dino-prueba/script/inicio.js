document.addEventListener('DOMContentLoaded', () => {
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
