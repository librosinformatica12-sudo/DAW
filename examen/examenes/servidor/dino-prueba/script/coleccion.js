document.addEventListener('DOMContentLoaded', () => {
    const entrada = document.getElementById('filtro-coleccion');
    const rejilla = document.getElementById('cartas-grid');
    const contador = document.getElementById('contador-coleccion');

    if (!entrada || !rejilla) {
        return;
    }

    const cartas = Array.from(rejilla.children);
    const total = cartas.length;

    const normalizar = (valor) => String(valor ?? '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');

    const aplicar = () => {
        const consulta = normalizar(entrada.value.trim());
        let visibles = 0;

        cartas.forEach((carta) => {
            const texto = normalizar(`${carta.dataset.especie} ${carta.dataset.apodo}`);
            const coincide = consulta === '' || texto.includes(consulta);
            carta.hidden = !coincide;
            if (coincide) {
                visibles++;
            }
        });

        if (contador) {
            contador.textContent = `${visibles} de ${total} especies`;
        }
    };

    entrada.addEventListener('input', aplicar);
});
