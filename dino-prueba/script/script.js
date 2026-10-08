document.addEventListener('DOMContentLoaded', () => {

    // ---------- ELEMENTOS ----------
    const container = document.querySelector('.container');
    const btnLogin = document.querySelector('.btn[data-ir="login"]');
    const btnRegister = document.querySelector('.btn[data-ir="registro"]');

    const formLogin = document.getElementById('form-login');
    const formRegistro = document.getElementById('form-registro');
    const linksIrRegister = document.querySelectorAll('a[data-ir="registro"]');
    const linksIrLogin = document.querySelectorAll('a[data-ir="login"]');

    // ---------- CAMBIO DE INFO DEL HERO (lado izquierdo) ----------
    const heroTitulo = document.getElementById('hero-title');
    const heroDescripcion = document.getElementById('hero-description');
    const features = document.querySelectorAll('main.pagina .hero ul li');

    const textosHero = {
        login: {
            titulo: 'Bienvenido al <span>Portal del Coleccionista</span>',
            descripcion: 'Reclama una carta nueva cada día, consulta tu colección y descubre las estadísticas de cada dinosaurio... todo desde un solo lugar.',
            features: [
                { titulo: 'Carta diaria', texto: 'Abre tu sobre y consigue un dinosaurio nuevo.' },
                { titulo: 'Mi colección', texto: 'Consulta las cartas que ya tienes.' },
                { titulo: 'DinoPedia', texto: 'Explora el catálogo completo de especies.' }
            ]
        },
        registro: {
            titulo: '¡Regístrate <span>ahora</span>!',
            descripcion: 'Crea tu cuenta y empieza a reclamar cartas nuevas cada día. ¡No te pierdas la diversión!',
            features: [
                { titulo: 'Crea tu cuenta', texto: 'Solo necesitas un usuario, un email y una contraseña.' },
                { titulo: 'Tu primer sobre', texto: 'Empieza a reclamar cartas desde el primer día.' },
                { titulo: 'Colecciónalos todos', texto: 'Completa tu colección y descubre cada especie.' }
            ]
        }
    };

    function cambiarHero(modo) {
        const t = textosHero[modo];
        if (!t || !heroTitulo || !heroDescripcion) return;

        heroTitulo.innerHTML = t.titulo;
        heroDescripcion.textContent = t.descripcion;

        features.forEach((li, i) => {
            if (!t.features[i]) return;
            li.querySelector('b').textContent = t.features[i].titulo;
            li.querySelector('small').textContent = t.features[i].texto;
        });
    }
    
    // ---------- CAMBIO DE PESTAÑA ----------
    function mostrarLogin() {
        if (formLogin) formLogin.hidden = false;
        if (formRegistro) formRegistro.hidden = true;

        if (btnLogin && btnRegister) {
            btnLogin.classList.add('activo');
            btnLogin.classList.remove('inactivo');
            btnRegister.classList.add('inactivo');
            btnRegister.classList.remove('activo');
        }
        if (container) container.setAttribute('data-panel', 'login');
        cambiarHero('login');
    }

    function mostrarRegistro() {
        if (formLogin) formLogin.hidden = true;
        if (formRegistro) formRegistro.hidden = false;

        if (btnLogin && btnRegister) {
            btnRegister.classList.add('activo');
            btnRegister.classList.remove('inactivo');
            btnLogin.classList.add('inactivo');
            btnLogin.classList.remove('activo');
        }
        if (container) container.setAttribute('data-panel', 'registro');
        cambiarHero('registro');
    }

    if (btnLogin) btnLogin.addEventListener('click', mostrarLogin);
    if (btnRegister) btnRegister.addEventListener('click', mostrarRegistro);

    linksIrRegister.forEach((link) => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            mostrarRegistro();
        });
    });

    linksIrLogin.forEach((link) => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            mostrarLogin();
        });
    });

    // Estado inicial según data-panel
    const panelInicial = container ? container.getAttribute('data-panel') : 'login';
    if (panelInicial === 'registro') {
        mostrarRegistro();
    } else {
        mostrarLogin();
    }

    // ---------- MOSTRAR / OCULTAR CONTRASEÑA ----------
    document.querySelectorAll('button[data-para]').forEach((boton) => {
        const input = document.getElementById(boton.dataset.para);
        if (!input) return;

        function ocultar() {
            input.type = 'password';
            boton.textContent = 'Mostrar';
        }

        boton.style.display = input.value.length > 0 ? 'inline-block' : 'none';

        input.addEventListener('input', () => {
            if (input.value.length > 0) {
                boton.style.display = 'inline-block';
            } else {
                boton.style.display = 'none';
                ocultar();
            }
        });

        boton.addEventListener('click', () => {
            if (input.type === 'password') {
                input.type = 'text';
                boton.textContent = 'Ocultar';
            } else {
                ocultar();
            }
        });

        if (input.form) {
            input.form.addEventListener('reset', () => {
                ocultar();
                boton.style.display = 'none';
            });
        }
    });

    // ---------- BARRA DE FUERZA DE CONTRASEÑA ----------
    const passReg = document.getElementById('reg-pass');
    const barra = document.getElementById('barra');

    if (passReg && barra) {
        passReg.addEventListener('input', () => {
            const v = passReg.value;
            let puntos = 0;
            if (v.length >= 8) puntos++;
            if (/[a-z]/.test(v) && /[A-Z]/.test(v)) puntos++;
            if (/\d/.test(v)) puntos++;
            if (/[^A-Za-z0-9]/.test(v)) puntos++;

            const colores = ['#ff4d4d', '#ff4d4d', '#ffa500', '#ffd700', '#2ecc71'];
            barra.style.width = (v.length === 0 ? 0 : puntos * 25) + '%';
            barra.style.backgroundColor = colores[puntos];
        });
    }

    // ---------- UTILIDADES Y AVISOS ----------
    function setMsg(input, texto) {
        if (!input) return true;
        const campo = input.closest('.campo');
        if (!campo) return true;
        let msg = campo.querySelector('.msg');

        if (!msg && texto !== '') {
            msg = document.createElement('small');
            msg.className = 'msg error-texto';
            campo.appendChild(msg);
        }

        if (msg) msg.textContent = texto;
        return texto === '';
    }

    function mostrarAviso(id, texto, ok) {
        const aviso = document.getElementById(id);
        if (aviso) {
            aviso.textContent = texto;
            aviso.className = `aviso ${ok ? 'ok' : 'error'}`;
            aviso.style.display = texto ? 'block' : 'none';
        }
    }

    async function enviarFormulario(url, form) {
        const formData = new FormData(form);
        const resp = await fetch(url, {
            method: 'POST',
            body: formData
        });

        if (!resp.ok) {
            throw new Error(`Error en el servidor (${resp.status})`);
        }

        return await resp.json();
    }

    // ---------- PETICIÓN LOGIN (AJAX) ----------
    if (formLogin) {
        formLogin.addEventListener('submit', async (e) => {
            e.preventDefault();

            const usuarioInput = document.getElementById('log-usuario');
            const passInput = document.getElementById('log-pass');

            if (!usuarioInput.value.trim() || !passInput.value) {
                mostrarAviso('aviso-login', 'Rellena usuario y contraseña.', false);
                return;
            }

            mostrarAviso('aviso-login', 'Iniciando sesión...', true);

            try {
                const res = await enviarFormulario('php/login.php', formLogin);

                if (res.ok) {
                    mostrarAviso('aviso-login', res.mensaje, true);
                    setTimeout(() => {
                        window.location.href = 'inicio.php';
                    }, 1000);
                } else {
                    mostrarAviso('aviso-login', res.mensaje, false);
                }
            } catch (err) {
                mostrarAviso('aviso-login', 'No se pudo conectar con el servidor.', false);
            }
        });
    }

    // ---------- PETICIÓN REGISTRO (AJAX) ----------
    if (formRegistro) {
        formRegistro.addEventListener('submit', async (e) => {
            e.preventDefault();

            const nombre = document.getElementById('reg-nombre');
            const apellido = document.getElementById('reg-apellido');
            const email = document.getElementById('reg-email');
            const usuario = document.getElementById('reg-usuario');
            const pass = document.getElementById('reg-pass');
            const pass2 = document.getElementById('reg-pass2');

            const validNombre = setMsg(nombre, nombre && nombre.value.trim() !== '' ? '' : 'El nombre es obligatorio.');
            const validApellido = setMsg(apellido, apellido && apellido.value.trim() !== '' ? '' : 'El apellido es obligatorio.');
            const validUsuario = setMsg(usuario, usuario && usuario.value.trim().length >= 3 ? '' : 'Mínimo 3 caracteres.');
            const validEmail = setMsg(email, email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim()) ? '' : 'Correo no válido.');
            const validPass = setMsg(pass, pass && pass.vxalue.length >= 4 ? '' : 'Mínimo 4 caracteres.');
            const validPass2 = setMsg(pass2, pass2 && pass2.value === pass.value && pass2.value !== '' ? '' : 'Las contraseñas no coinciden.');

            const esValido = validNombre && validApellido && validUsuario && validEmail && validPass && validPass2;

            if (!esValido) {
                mostrarAviso('aviso-registro', 'Revisa los campos del formulario.', false);
                return;
            }

            mostrarAviso('aviso-registro', 'Procesando registro...', true);

            try {
                const res = await enviarFormulario('php/registro.php', formRegistro);

                if (res.ok) {
                    mostrarAviso('aviso-registro', res.mensaje, true);
                    formRegistro.reset();

                    setTimeout(() => {
                        mostrarLogin();
                        mostrarAviso('aviso-login', 'Cuenta creada con éxito. Ya puedes acceder.', true);
                    }, 2000);
                } else {
                    mostrarAviso('aviso-registro', res.mensaje, false);
                }
            } catch (err) {
                mostrarAviso('aviso-registro', 'No se pudo completar el registro. Error de red.', false);
            }
        });
    }
});