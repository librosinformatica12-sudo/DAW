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
     * Envía un formulario vía AJAX (Fetch API) a un backend que responde JSON
     */
    async function enviar(url, form) {
        const formData = new FormData(form);
        const res = await fetch(url, {
            method: 'POST',
            body: formData
        });

        // Si la respuesta no es 200 OK, lanzamos error con el estatus
        if (!res.ok) {
            throw new Error(`HTTP_${res.status}`);
        }

        return await res.json();
    }

    /**
     * Muestra el mensaje general de aviso dentro del div especificado (#aviso-login o #aviso-registro)
     */
    function mostrarAviso(idAviso, mensaje, esExito) {
        let aviso = document.getElementById(idAviso);

        // Si no existe el contenedor de aviso en registro, lo crea dinámicamente
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
     * Marca/desmarca errores visuales en los campos individuales de formulario
     */
    function setMsg(inputElement, mensajeError) {
        if (!inputElement) return false;

        const contenedorCampo = inputElement.closest('.campo');
        let msgSpan = contenedorCampo ? contenedorCampo.querySelector('.error-texto') : null;

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

    // ---------- LOGIN ----------
      if (formLogin) {
          formLogin.addEventListener('submit', async (e) => {
              e.preventDefault();

              // Obtenemos las referencias a los inputs de forma segura
              const usuarioEl = document.getElementById('log-usuario');
              const passEl = document.getElementById('log-pass');

              const usuario = usuarioEl ? usuarioEl.value.trim() : '';
              const pass = passEl ? passEl.value : '';

              // Validación de campos vacíos
              if (usuario === '' || pass === '') {
                  mostrarAviso('aviso-login', 'Rellena usuario y contraseña.', false);
                  return;
              }

              try {
                  // Envió de datos al servidor
                  const r = await enviar('php/login.php', formLogin);
                  mostrarAviso('aviso-login', r.mensaje, r.ok);

                  if (r.ok) {
                      formLogin.reset();
                      // Redirige al inicio tras 1 segundo de éxito
                      setTimeout(() => {
                          window.location.href = 'inicio.php';
                      }, 1000);
                  }
              } catch (err) {
                  console.error('Error Login:', err);
                  // Si la función enviar lanza un error de status 404 (archivo no encontrado)
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

            // Validación de los campos exactos del HTML
            const resultados = [
                setMsg(regUsuario, regUsuario.value.trim().length >= 3 ? '' : 'Mínimo 3 caracteres.'),
                setMsg(regEmail, /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(regEmail.value.trim()) ? '' : 'Correo no válido.'),
                setMsg(regPass, regPass.value.length >= 4 ? '' : 'Mínimo 4 caracteres.'),
                setMsg(regPass2, (regPass2.value === regPass.value && regPass2.value !== '') ? '' : 'Las contraseñas no coinciden.')
            ];

            // Cancelar si algún campo no cumple con las validaciones
            if (!resultados.every(Boolean)) {
                mostrarAviso('aviso-registro', 'Revisa los campos marcados.', false);
                return;
            }

            try {
                // Envía siempre a 'php/registro.php'
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
                if (err.message.includes('404')) {
                    mostrarAviso('aviso-registro', 'No se encontró el archivo php/registro.php.', false);
                } else {
                    mostrarAviso('aviso-registro', 'No se pudo conectar con el servidor.', false);
                }
            }
        });
    }

    // ---------- MOSTRAR / OCULTAR CONTRASEÑA ----------
    document.querySelectorAll('button[data-para]').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-para');
            const inputTarget = document.getElementById(targetId);

            if (inputTarget) {
                if (inputTarget.type === 'password') {
                    inputTarget.type = 'text';
                    btn.textContent = 'Ocultar';
                } else {
                    inputTarget.type = 'password';
                    btn.textContent = 'Mostrar';
                }
            }
        });
    });

    // ---------- CONMUTACIÓN DE PESTAÑAS (TABS) ----------
    function cambiarTab(destino) {
        if (container) {
            container.setAttribute('data-panel', destino);
        }

        if (destino === 'login') {
            if (formLogin) formLogin.hidden = false;
            if (formRegistro) formRegistro.hidden = true;
        } else if (destino === 'registro') {
            if (formLogin) formLogin.hidden = true;
            if (formRegistro) formRegistro.hidden = false;
        }
    }

    // Manejar clics en botones de pestañas o enlaces con data-ir="login" / data-ir="registro"
    document.querySelectorAll('[data-ir]').forEach(elemento => {
        elemento.addEventListener('click', (e) => {
            e.preventDefault();
            const destino = elemento.getAttribute('data-ir');
            cambiarTab(destino);
        });
    });

});