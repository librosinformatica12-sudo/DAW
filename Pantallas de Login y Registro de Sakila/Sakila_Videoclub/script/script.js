document.addEventListener('DOMContentLoaded', () => {

    // ---------- ELEMENTOS ----------
    const titulo = document.querySelector('.container > h1');
    const botonesTab = document.querySelectorAll('.container > .btn');
    const btnLogin = botonesTab[0];
    const btnRegister = botonesTab[1];

    const formLogin = document.getElementById('form-login');
    const formRegistro = document.getElementById('form-registro');
    const linkRegister = document.querySelector('#form-login .register-link a');
    const linkLogin = document.querySelector('#form-registro .register-link a');

    // ---------- CAMBIO DE PESTAÑA ----------
    function mostrarLogin() {
        formLogin.hidden = false;
        formRegistro.hidden = true;
        titulo.textContent = 'Iniciar Sesión';
        btnLogin.classList.add('activo');
        btnLogin.classList.remove('inactivo');
        btnRegister.classList.add('inactivo');
        btnRegister.classList.remove('activo');
    }

    function mostrarRegistro() {
        formLogin.hidden = true;
        formRegistro.hidden = false;
        titulo.textContent = 'Registrarse';
        btnRegister.classList.add('activo');
        btnRegister.classList.remove('inactivo');
        btnLogin.classList.add('inactivo');
        btnLogin.classList.remove('activo');
    }

    btnLogin.addEventListener('click', mostrarLogin);
    btnRegister.addEventListener('click', mostrarRegistro);

    linkRegister.addEventListener('click', (e) => {
        e.preventDefault();
        mostrarRegistro();
    });
    linkLogin.addEventListener('click', (e) => {
        e.preventDefault();
        mostrarLogin();
    });

    mostrarLogin(); // estado inicial

    // ---------- MOSTRAR / OCULTAR CONTRASEÑA ----------
    document.querySelectorAll('button[data-para]').forEach((boton) => {
        const input = document.getElementById(boton.dataset.para);
        if (!input) return;

        boton.style.display = input.value.length > 0 ? 'inline-block' : 'none';

        input.addEventListener('input', () => {
            if (input.value.length > 0) {
                boton.style.display = 'inline-block';
            } else {
                boton.style.display = 'none';
                input.type = 'password';
                boton.textContent = 'Mostrar';
            }
        });

        boton.addEventListener('click', () => {
            if (input.type === 'password') {
                input.type = 'text';
                boton.textContent = 'Ocultar';
            } else {
                input.type = 'password';
                boton.textContent = 'Mostrar';
            }
        });
    });

    // ---------- BARRA DE FUERZA DE CONTRASEÑA ----------
    const passReg = document.getElementById('reg-pass');
    const barra = document.getElementById('barra');

    passReg.addEventListener('input', () => {
        const v = passReg.value;
        let puntos = 0;
        if (v.length >= 8) puntos++;
        if (/[a-z]/.test(v) && /[A-Z]/.test(v)) puntos++;
        if (/\d/.test(v)) puntos++;
        if (/[^A-Za-z0-9]/.test(v)) puntos++;

        const colores = ['red', 'red', 'orange', 'gold', 'green'];
        barra.style.width = (v.length === 0 ? 0 : puntos * 25) + '%';
        barra.style.background = colores[puntos];
    });

    // ---------- UTILIDADES ----------
    function setMsg(input, texto) {
        const msg = input.closest('.campo').querySelector('.msg');
        msg.textContent = texto;
        return texto === '';
    }

    function mostrarAviso(id, texto, ok) {
        const aviso = document.getElementById(id);
        aviso.textContent = texto;
        aviso.style.color = ok ? 'green' : 'red';
    }

    // Envía el formulario a PHP y devuelve el JSON  (NUEVO)
    async function enviar(url, form) {
        const resp = await fetch(url, { method: 'POST', body: new FormData(form) });
        return await resp.json();
    }

    // ---------- LOGIN (SUSTITUIDO) ----------
    formLogin.addEventListener('submit', async (e) => {
        e.preventDefault();
        const usuario = document.getElementById('log-usuario').value.trim();
        const pass = document.getElementById('log-pass').value;

        if (usuario === '' || pass === '') {
            mostrarAviso('aviso-login', 'Rellena usuario y contraseña.', false);
            return;
        }

        try {
            const r = await enviar('php/login.php', formLogin);
            mostrarAviso('aviso-login', r.mensaje, r.ok);
            if (r.ok) {
                formLogin.reset();
            }
        } catch (err) {
            console.error(err);
            mostrarAviso('aviso-login', 'No se pudo conectar con el servidor.', false);
        }
    });

    // ---------- REGISTRO (SUSTITUIDO, sin tienda) ----------
    formRegistro.addEventListener('submit', async (e) => {
        e.preventDefault();

        const nombre = document.getElementById('reg-nombre');
        const apellido = document.getElementById('reg-apellido');
        const email = document.getElementById('reg-email');
        const usuario = document.getElementById('reg-usuario');
        const pass = document.getElementById('reg-pass');
        const pass2 = document.getElementById('reg-pass2');

        const resultados = [
            setMsg(nombre, nombre.value.trim() === '' ? 'Escribe tu nombre.' : ''),
            setMsg(apellido, apellido.value.trim() === '' ? 'Escribe tu apellido.' : ''),
            setMsg(email, /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim()) ? '' : 'Correo no válido.'),
            setMsg(usuario, usuario.value.trim().length >= 3 ? '' : 'Mínimo 3 caracteres.'),
            setMsg(pass, pass.value.length >= 8 ? '' : 'Mínimo 8 caracteres.'),
            setMsg(pass2, pass2.value === pass.value && pass2.value !== '' ? '' : 'Las contraseñas no coinciden.')
        ];

        if (!resultados.every(Boolean)) {
            mostrarAviso('aviso-registro', 'Revisa los campos marcados.', false);
            return;
        }

        try {
            const r = await enviar('php/registro.php', formRegistro);
            mostrarAviso('aviso-registro', r.mensaje, r.ok);
            if (r.ok) {
                formRegistro.reset();
                barra.style.width = '0%';
            }
        } catch (err) {
            console.error(err);
            mostrarAviso('aviso-registro', 'No se pudo conectar con el servidor.', false);
        }
    });
});