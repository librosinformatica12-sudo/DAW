document.addEventListener('DOMContentLoaded', () => {

  // ---------- REFERENCIAS A ELEMENTOS ----------
  const container = document.querySelector('.container');
  const formLogin = document.getElementById('form-login');
  const formRegistro = document.getElementById('form-registro');

  // Inputs de Login
  const logUsuario = document.getElementById('log-usuario');
  const logPass = document.getElementById('log-pass');

  // Inputs de Registro
  const regUsuario = document.getElementById('reg-usuario');
  const regEmail = document.getElementById('reg-email');
  const regPass = document.getElementById('reg-pass');
  const regPass2 = document.getElementById('reg-pass2');

  // ---------- FUNCIONES AUXILIARES ----------

  /**
   * Envía un formulario vía AJAX (Fetch API)
   */
  async function enviar(url, form) {
    const formData = new FormData(form);
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    });

    if (!res.ok) {
      throw new Error(`HTTP_${res.status}`);
    }

    return await res.json();
  }

  /**
   * Muestra aviso general (#aviso-login o #aviso-registro)
   */
  function mostrarAviso(idAviso, mensaje, esExito) {
    let aviso = document.getElementById(idAviso);

    if (!aviso && idAviso === 'aviso-registro' && formRegistro) {
      aviso = document.createElement('div');
      aviso.id = 'aviso-registro';
      aviso.setAttribute('role', 'status');

      const btnEnviar = formRegistro.querySelector('button[type="submit"]');
      if (btnEnviar) {
        formRegistro.insertBefore(aviso, btnEnviar.nextSibling);
      } else {
        formRegistro.appendChild(aviso);
      }
    }

    if (!aviso) return;

    aviso.textContent = mensaje;
    aviso.className = `aviso ${esExito ? 'ok' : 'error'}`;
    aviso.style.display = mensaje ? 'block' : 'none';
  }

  /**
   * Marca/desmarca errores individuales en los campos
   */
  function setMsg(inputElement, mensajeError) {
    if (!inputElement) return false;

    const contenedorCampo = inputElement.closest('.campo');
    let msgSpan = contenedorCampo ? contenedorCampo.querySelector('.error-texto, .msg') : null;

    if (mensajeError) {
      inputElement.classList.add('campo-error');

      if (contenedorCampo) {
        if (!msgSpan) {
          msgSpan = document.createElement('span');
          msgSpan.className = 'error-texto';
          msgSpan.style.color = '#ff4d4d';
          msgSpan.style.fontSize = '0.82em';
          msgSpan.style.marginTop = '4px';
          msgSpan.style.display = 'block';
          contenedorCampo.appendChild(msgSpan);
        }
        msgSpan.textContent = mensajeError;
      }
      return false;
    } else {
      inputElement.classList.remove('campo-error');
      if (msgSpan) {
        msgSpan.textContent = '';
      }
      return true;
    }
  }

  // ---------- FUERZA DE LA CONTRASEÑA ----------
  const barra = document.getElementById('barra');
  if (regPass && barra) {
    regPass.addEventListener('input', () => {
      const v = regPass.value;
      let n = 0;
      if (v.length >= 8) n++;
      if (/[A-Z]/.test(v) && /[a-z]/.test(v)) n++;
      if (/\d/.test(v)) n++;
      if (/[^A-Za-z0-9]/.test(v)) n++;
      barra.style.width = (v ? Math.max(n, 1) * 25 : 0) + '%';
      barra.style.background = ['#b3361b', '#b3361b', '#e3a21a', '#6aa84f', '#2f7d4a'][v ? n : 0];
    });
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

  // ---------- CONMUTACIÓN DE PESTAÑAS (TABS) ----------
  function cambiarTab(destino) {
    if (container) {
      container.setAttribute('data-panel', destino);
      container.dataset.panel = destino;
    }

    if (formLogin) formLogin.hidden = (destino !== 'login');
    if (formRegistro) formRegistro.hidden = (destino !== 'registro');

    document.querySelectorAll('.tabs .btn').forEach(b => {
      const activo = b.dataset.ir === destino;
      b.classList.toggle('activo', activo);
      b.setAttribute('aria-selected', activo);
    });
  }

  document.querySelectorAll('[data-ir]').forEach(el => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      cambiarTab(el.dataset.ir);
    });
  });

  // ---------- LOGIN ----------
  if (formLogin) {
    formLogin.addEventListener('submit', async (e) => {
      e.preventDefault();

      const usuario = logUsuario ? logUsuario.value.trim() : '';
      const pass = logPass ? logPass.value : '';

      if (usuario === '' || pass === '') {
        mostrarAviso('aviso-login', 'Rellena usuario y contraseña.', false);
        return;
      }

      try {
        const r = await enviar('php/login.php', formLogin);
        mostrarAviso('aviso-login', r.mensaje, r.ok);

        if (r.ok) {
          formLogin.reset();
          setTimeout(() => {
            window.location.href = 'inicio.php';
          }, 1000);
        }
      } catch (err) {
        console.error('Error Login:', err);
        if (err.message && err.message.includes('404')) {
          mostrarAviso('aviso-login', 'Error: No se encuentra el archivo php/login.php', false);
        } else {
          mostrarAviso('aviso-login', 'No se pudo conectar con el servidor.', false);
        }
      }
    });
  }

  // ---------- REGISTRO ----------
  if (formRegistro) {
    formRegistro.addEventListener('submit', async (e) => {
      e.preventDefault();

      const resultados = [
        setMsg(regUsuario, regUsuario && regUsuario.value.trim().length >= 3 ? '' : 'Mínimo 3 caracteres.'),
        setMsg(regEmail, regEmail && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(regEmail.value.trim()) ? '' : 'Correo no válido.'),
        setMsg(regPass, regPass && regPass.value.length >= 8 ? '' : 'Mínimo 8 caracteres.'),
        setMsg(regPass2, regPass2 && regPass && regPass2.value === regPass.value && regPass2.value !== '' ? '' : 'Las contraseñas no coinciden.')
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
          setTimeout(() => {
            cambiarTab('login');
          }, 1500);
        }
      } catch (err) {
        console.error('Error Registro:', err);
        if (err.message && err.message.includes('404')) {
          mostrarAviso('aviso-registro', 'No se encontró el archivo php/registro.php.', false);
        } else {
          mostrarAviso('aviso-registro', 'No se pudo conectar con el servidor.', false);
        }
      }
    });
  }

});