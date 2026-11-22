document.addEventListener('DOMContentLoaded', () => {
    const aviso = document.getElementById('aviso-dinopedia');

    document.querySelectorAll('.form-anadir').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const datos = new FormData(form);

            if (aviso) {
                aviso.textContent = 'Añadiendo carta...';
                aviso.className = 'aviso ok';
            }

            try {
                const respuesta = await fetch(form.action, { method: 'POST', body: datos });
                const resultado = await respuesta.json();

                if (aviso) {
                    aviso.textContent = resultado.mensaje;
                    aviso.className = `aviso ${resultado.ok ? 'ok' : 'error'}`;
                }
            } catch (error) {
                if (aviso) {
                    aviso.textContent = 'No se pudo conectar con el servidor.';
                    aviso.className = 'aviso error';
                }
            }
        });
    });
});
