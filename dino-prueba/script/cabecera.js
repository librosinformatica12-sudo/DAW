document.addEventListener('DOMContentLoaded', () => {
    const boton = document.getElementById('btn-usuario');
    const menu = document.getElementById('menu-usuario');

    if (!boton || !menu) {
        return;
    }

    const cerrar = () => {
        menu.hidden = true;
        boton.classList.remove('abierto');
        boton.setAttribute('aria-expanded', 'false');
    };

    boton.addEventListener('click', (evento) => {
        evento.stopPropagation();
        const seAbre = menu.hidden;
        menu.hidden = !seAbre;
        boton.classList.toggle('abierto', seAbre);
        boton.setAttribute('aria-expanded', String(seAbre));
    });

    document.addEventListener('click', (evento) => {
        if (!menu.hidden && !menu.contains(evento.target)) {
            cerrar();
        }
    });

    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') {
            cerrar();
        }
    });
});
