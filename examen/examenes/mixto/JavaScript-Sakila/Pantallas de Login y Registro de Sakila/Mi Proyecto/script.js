// ====== Referencias al DOM ======
const titulo = document.querySelector('.container h1');
const [btnLogin, btnRegistrar] = document.querySelectorAll('.btn');
const formLogin = document.querySelector('form:not(#form-registro)');
const formRegistro = document.getElementById('form-registro');
const enlaceRegistro = document.querySelector('.register-link a');

// ====== Mostrar / ocultar formularios ======
function mostrarLogin() {
  titulo.textContent = 'Iniciar Sesión';
  formLogin.hidden = false;
  formRegistro.hidden = true;
  btnLogin.classList.add('activo');
  btnRegistrar.classList.remove('activo');
}

function mostrarRegistro() {
  titulo.textContent = 'Registro';
  formLogin.hidden = true;
  formRegistro.hidden = false;
  btnRegistrar.classList.add('activo');
  btnLogin.classList.remove('activo');
}

btnLogin.addEventListener('click', mostrarLogin);
btnRegistrar.addEventListener('click', mostrarRegistro);
enlaceRegistro.addEventListener('click', (e) => {
  e.preventDefault();
  mostrarRegistro();
});

// Estado inicial: login visible, registro oculto
mostrarLogin();

// ====== Utilidades ======
function ponerError(input, texto) {
  const msg = input.closest('.campo').querySelector('.msg');
  msg.textContent = texto;
  msg.style.color = texto ? 'red' : '';
  input.style.borderColor = texto ? 'red' : '';
  return !texto; // true si es válido
}

// ====== LOGIN ======
const avisoLogin = document.createElement('div');
avisoLogin.setAttribute('role', 'status');
formLogin.appendChild(avisoLogin);

formLogin.addEventListener('submit', (e) => {
  e.preventDefault();
  const usuario = formLogin.querySelector('input[type="text"]').value.trim();
  const pass = formLogin.querySelector('input[type="password"]').value;

  if (usuario.length < 3 || pass.length < 1) {
    avisoLogin.textContent = 'Introduce usuario y contraseña.';
    avisoLogin.style.color = 'red';
    return;
  }

  // Aquí iría la llamada a tu servidor (fetch) para comprobar las credenciales
  avisoLogin.textContent = `Bienvenido, ${usuario}.`;
  avisoLogin.style.color = 'green';
});

// ====== REGISTRO ======
const regNombre = document.getElementById('reg-nombre');
const regEmail = document.getElementById('reg-email');
const regPass = document.getElementById('reg-pass');
const regPass2 = document.getElementById('reg-pass2');
const barra = document.getElementById('barra');
const avisoRegistro = document.getElementById('aviso-registro');

function validarNombre() {
  return ponerError(regNombre, regNombre.value.trim().length < 2 ? 'Escribe tu nombre (mín. 2 letras).' : '');
}

function validarEmail() {
  const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(regEmail.value.trim());
  return ponerError(regEmail, ok ? '' : 'Introduce un correo válido.');
}

function validarPass() {
  return ponerError(regPass, regPass.value.length < 8 ? 'Mínimo 8 caracteres.' : '');
}

function validarPass2() {
  return ponerError(regPass2, regPass2.value !== regPass.value || !regPass2.value ? 'Las contraseñas no coinciden.' : '');
}

// Barra de fuerza de la contraseña
function actualizarFuerza() {
  const p = regPass.value;
  let puntos = 0;
  if (p.length >= 8) puntos++;
  if (/[A-Z]/.test(p) && /[a-z]/.test(p)) puntos++;
  if (/\d/.test(p)) puntos++;
  if (/[^A-Za-z0-9]/.test(p)) puntos++;

  const colores = ['#e53935', '#fb8c00', '#fdd835', '#43a047'];
  barra.style.display = 'block';
  barra.style.height = '6px';
  barra.style.borderRadius = '3px';
  barra.style.transition = 'width .3s';
  barra.style.width = p ? `${Math.max(puntos, 1) * 25}%` : '0';
  barra.style.background = colores[Math.max(puntos, 1) - 1];
}

regNombre.addEventListener('blur', validarNombre);
regEmail.addEventListener('blur', validarEmail);
regPass.addEventListener('input', () => { actualizarFuerza(); validarPass(); });
regPass2.addEventListener('input', validarPass2);

// Botón Mostrar / Ocultar contraseña
document.querySelectorAll('[data-para]').forEach((btn) => {
  btn.addEventListener('click', () => {
    const input = document.getElementById(btn.dataset.para);
    const oculto = input.type === 'password';
    input.type = oculto ? 'text' : 'password';
    btn.textContent = oculto ? 'Ocultar' : 'Mostrar';
  });
});

// Envío del registro
formRegistro.addEventListener('submit', (e) => {
  e.preventDefault();

  const todoOk = [validarNombre(), validarEmail(), validarPass(), validarPass2()].every(Boolean);

  if (!todoOk) {
    avisoRegistro.textContent = 'Revisa los campos marcados en rojo.';
    avisoRegistro.style.color = 'red';
    return;
  }

  // Aquí iría la llamada a tu servidor (fetch) para guardar el usuario
  avisoRegistro.textContent = `Cuenta creada para ${regNombre.value.trim()}. Ya puedes iniciar sesión.`;
  avisoRegistro.style.color = 'green';
  formRegistro.reset();
  barra.style.width = '0';

  setTimeout(() => {
    avisoRegistro.textContent = '';
    mostrarLogin();
  }, 2000);
});