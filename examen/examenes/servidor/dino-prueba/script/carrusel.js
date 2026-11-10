/* ============================================================
   carrusel.js · Carrusel de sobres
   - Flechas anterior / siguiente
   - Puntos para saltar a un sobre
   - Teclado (← →) y deslizar con el dedo / trackpad
   ============================================================ */
(function () {
    'use strict';

    const carrusel = document.getElementById('carrusel');
    const pista    = document.getElementById('carrusel-pista');
    const btnPrev  = document.getElementById('sobre-prev');
    const btnNext  = document.getElementById('sobre-next');
    const puntos   = document.getElementById('carrusel-puntos');
    const contador = document.getElementById('sobres-contador');

    if (!carrusel || !pista) return;

    const slides = Array.from(pista.querySelectorAll('.sobre-slide'));
    const total  = slides.length;
    let actual   = 0;

    /* ---------- Puntos ---------- */
    const botonesPunto = slides.map((_, i) => {
        const li  = document.createElement('li');
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.setAttribute('aria-label', 'Ir al sobre ' + (i + 1));
        btn.addEventListener('click', () => irA(i));
        li.appendChild(btn);
        puntos.appendChild(li);
        return btn;
    });

    /* ---------- Mostrar un sobre ---------- */
    function irA(indice) {
        actual = Math.max(0, Math.min(total - 1, indice));
        pista.scrollTo({
            left: slides[actual].offsetLeft - pista.offsetLeft,
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'
        });
        actualizarEstado();
    }

    function actualizarEstado() {
        slides.forEach((s, i) => s.classList.toggle('activo', i === actual));
        botonesPunto.forEach((b, i) => {
            b.classList.toggle('activo', i === actual);
            b.setAttribute('aria-current', i === actual ? 'true' : 'false');
        });
        if (contador) contador.textContent = (actual + 1) + ' / ' + total;
        if (btnPrev) btnPrev.disabled = actual === 0;
        if (btnNext) btnNext.disabled = actual === total - 1;
    }

    /* ---------- Si el usuario desliza, detectamos en qué sobre está ---------- */
    let temporizador;
    pista.addEventListener('scroll', () => {
        clearTimeout(temporizador);
        temporizador = setTimeout(() => {
            const ancho = pista.clientWidth || 1;
            const indice = Math.round(pista.scrollLeft / ancho);
            if (indice !== actual) {
                actual = Math.max(0, Math.min(total - 1, indice));
                actualizarEstado();
            }
        }, 80);
    }, { passive: true });

    /* ---------- Controles ---------- */
    if (btnPrev) btnPrev.addEventListener('click', () => irA(actual - 1));
    if (btnNext) btnNext.addEventListener('click', () => irA(actual + 1));

    carrusel.addEventListener('keydown', (ev) => {
        if (ev.key === 'ArrowLeft')  { ev.preventDefault(); irA(actual - 1); }
        if (ev.key === 'ArrowRight') { ev.preventDefault(); irA(actual + 1); }
    });

    window.addEventListener('resize', () => irA(actual));

    actualizarEstado();
})();
