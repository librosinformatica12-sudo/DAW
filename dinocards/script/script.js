document.addEventListener('DOMContentLoaded', () => {

  // ---- Pestañas Iniciar sesión / Registrarse ----
  const contenedor = document.querySelector('.container');
  const formLogin = document.getElementById('form-login');
  const formRegistro = document.getElementById('form-registro');

  function mostrarPanel(nombre) {
    if (!contenedor) return;
    contenedor.dataset.panel = nombre;
    if (formLogin) formLogin.hidden = nombre !== 'login';
    if (formRegistro) formRegistro.hidden = nombre !== 'registro';
    document.querySelectorAll('.tabs .btn').forEach(b => {
      const activo = b.dataset.ir === nombre;
      b.classList.toggle('activo', activo);
      b.setAttribute('aria-selected', activo);
    });
  }

  document.querySelectorAll('[data-ir]').forEach(el =>
    el.addEventListener('click', e => { 
      e.preventDefault(); 
      mostrarPanel(el.dataset.ir); 
    })
  );

  if (contenedor && contenedor.dataset.panel) {
    mostrarPanel(contenedor.dataset.panel);
  }

  const olvide = document.getElementById('olvide');
  if (olvide) olvide.addEventListener('click', e => e.preventDefault());

  // ---- Fuerza de la contraseña ----
  const pass = document.getElementById('reg-pass');
  const barra = document.getElementById('barra');
  if (pass && barra) {
    pass.addEventListener('input', () => {
      const v = pass.value;
      let n = 0;
      if (v.length >= 8) n++;
      if (/[A-Z]/.test(v) && /[a-z]/.test(v)) n++;
      if (/\d/.test(v)) n++;
      if (/[^A-Za-z0-9]/.test(v)) n++;
      barra.style.width = (v ? Math.max(n, 1) * 25 : 0) + '%';
      barra.style.background = ['#b3361b', '#b3361b', '#e3a21a', '#6aa84f', '#2f7d4a'][v ? n : 0];
    });
  }

  // ---- Mostrar / Ocultar Contraseña ----
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

    boton.addEventListener('click', (e) => {
      e.preventDefault();
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

  // ---- Función auxiliar de errores de validación ----
  function error(input, texto) {
    if (!input) return true;
    const campo = input.closest('.campo');
    if (campo) {
      campo.classList.toggle('invalido', !!texto);
      const msg = campo.querySelector('.msg');
      if (msg) msg.textContent = texto || '';
    }
    return !texto;
  }

  // ---- ENVÍO POST MEDIANTE FETCH (AJAX) ----
  
  // 1. Envío POST de Registro
  if (formRegistro) {
    formRegistro.addEventListener('submit', async (e) => {
      e.preventDefault(); // Evitamos recargar la página o causar un error 404

      const f = formRegistro.elements;
      const u = f.usuario ? f.usuario.value.trim() : '';
      const checks = [
        error(f.usuario, u.length < 3 || u.length > 16 ? 'Entre 3 y 16 caracteres.' : ''),
        error(f.email, /^\S+@\S+\.\S+$/.test(f.email ? f.email.value.trim() : '') ? '' : 'Correo no válido.'),
        error(f.contrasena, f.contrasena && f.contrasena.value.length < 8 ? 'Mínimo 8 caracteres.' : ''),
        error(f.confirmaContrasena, f.confirmaContrasena && f.confirmaContrasena.value !== f.contrasena.value ? 'No coinciden.' : '')
      ];

      if (checks.includes(false)) return;

      const formData = new FormData(formRegistro);

      try {
        const respuesta = await fetch('php/registro.php', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData
        });

        const data = await respuesta.json();

        if (data.ok) {
          window.location.href = 'inicio.php';
        } else {
          alert(data.mensaje || 'Error en el registro');
        }
      } catch (err) {
        console.error('Error enviando registro:', err);
      }
    });
  }

  // 2. Envío POST de Login
  if (formLogin) {
    formLogin.addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(formLogin);

      try {
        const respuesta = await fetch('php/login.php', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData
        });

        const data = await respuesta.json();

        if (data.ok) {
          window.location.href = 'inicio.php';
        } else {
          alert(data.mensaje || 'Error en el inicio de sesión');
        }
      } catch (err) {
        console.error('Error enviando login:', err);
      }
    });
  }

});