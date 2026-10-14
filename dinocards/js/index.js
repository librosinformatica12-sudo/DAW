/**
 * index.js
 * Controla la página de entrada: pestañas login/registro y envío por fetch.
 */
import { pedir, activarVerClave } from './main.js';

const formLogin = document.getElementById('form-login');
const formRegistro = document.getElementById('form-registro');
const aviso = document.getElementById('aviso');
const pestanas = document.querySelectorAll('.pestana');

activarVerClave();

/** Muestra un mensaje de aviso en la parte superior del formulario. */
function mostrarAviso(mensaje, tipo = 'error') {
    aviso.textContent = mensaje;
    aviso.className = 'aviso ' + tipo;
    aviso.hidden = false;
}

function ocultarAviso() {
    aviso.hidden = true;
}

// --- Cambio de pestaña ---
pestanas.forEach((pestana) => {
    pestana.addEventListener('click', () => {
        pestanas.forEach((p) => p.classList.toggle('activo', p === pestana));

        const panel = pestana.dataset.panel;
        formLogin.hidden = panel !== 'login';
        formRegistro.hidden = panel !== 'registro';
        ocultarAviso();
    });
});

// --- Iniciar sesión ---
formLogin.addEventListener('submit', async (evento) => {
    evento.preventDefault();

    const usuario = formLogin.usuario.value.trim();
    const contrasena = formLogin.contrasena.value;

    if (!usuario || !contrasena) {
        mostrarAviso('Rellena todos los campos.');
        return;
    }

    try {
        const datos = await pedir('api/login.php', {
            method: 'POST',
            body: new FormData(formLogin),
        });
        mostrarAviso(datos.mensaje, 'ok');
        window.location.href = 'inicio.php';
    } catch (error) {
        mostrarAviso(error.message);
    }
});

// --- Registro (con validación en el cliente) ---
formRegistro.addEventListener('submit', async (evento) => {
    evento.preventDefault();

    const usuario = formRegistro.usuario.value.trim();
    const email = formRegistro.email.value.trim();
    const clave1 = formRegistro.contrasena.value;
    const clave2 = formRegistro.confirma.value;

    if (usuario.length < 3) {
        mostrarAviso('El usuario debe tener al menos 3 caracteres.');
        return;
    }
    if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
        mostrarAviso('El correo no es válido.');
        return;
    }
    if (clave1.length < 4) {
        mostrarAviso('La contraseña debe tener al menos 4 caracteres.');
        return;
    }
    if (clave1 !== clave2) {
        mostrarAviso('Las contraseñas no coinciden.');
        return;
    }

    try {
        const datos = await pedir('api/registro.php', {
            method: 'POST',
            body: new FormData(formRegistro),
        });
        mostrarAviso(datos.mensaje, 'ok');
        formRegistro.reset();

        // Volvemos a la pestaña de login después de un momento
        setTimeout(() => {
            document.querySelector('.pestana[data-panel="login"]').click();
        }, 1200);
    } catch (error) {
        mostrarAviso(error.message);
    }
});
