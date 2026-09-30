const btnLogin = document.getElementById("btn-tab-login");
const btnRegister = document.getElementById("btn-tab-register");
const formLogin = document.getElementById("form-login");
const formRegistro = document.getElementById("form-registro");
const linkRegister = document.getElementById("link-register");
const linkLogin = document.getElementById("link-login");

function mostrarLogin() {
    formLogin.hidden = false;
    formRegistro.hidden = true;
    btnLogin.classList.add('active');
    btnRegister.classList.remove('active');
}

function mostrarRegistro() {
    formLogin.hidden = true;
    formRegistro.hidden = false;
    btnRegister.classList.add('active');
    btnLogin.classList.remove('active');
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

// PARTE DE MOSTRAR CONTRASEÑA (registro - contraseña)
document.addEventListener('DOMContentLoaded', () => {
    const inputPassReg = document.getElementById('reg-pass');
    const botonOjoReg = document.getElementById('reg-pass-ojo');

    inputPassReg.addEventListener('input', () => {
        if (inputPassReg.value.length > 0) {
            botonOjoReg.style.display = 'inline-block';
        } else {
            botonOjoReg.style.display = 'none';
            inputPassReg.type = 'password';
            botonOjoReg.textContent = 'Mostrar';
        }
    });

    if (inputPassReg.value.length === 0) {
        botonOjoReg.style.display = 'none';
    }

    botonOjoReg.addEventListener('click', () => {
        const tipoActual = inputPassReg.getAttribute('type');

        if (tipoActual === 'password') {
            inputPassReg.setAttribute('type', 'text');
            botonOjoReg.textContent = 'Ocultar';
        } else {
            inputPassReg.setAttribute('type', 'password');
            botonOjoReg.textContent = 'Mostrar';
        }
    });
});

// PARTE DE MOSTRAR CONTRASEÑA (registro - repetir contraseña)
document.addEventListener('DOMContentLoaded', () => {
    const inputPassReg2 = document.getElementById('reg-pass2');
    const botonOjoReg2 = document.getElementById('reg-pass2-ojo');

    inputPassReg2.addEventListener('input', () => {
        if (inputPassReg2.value.length > 0) {
            botonOjoReg2.style.display = 'inline-block';
        } else {
            botonOjoReg2.style.display = 'none';
            inputPassReg2.type = 'password';
            botonOjoReg2.textContent = 'Mostrar';
        }
    });

    if (inputPassReg2.value.length === 0) {
        botonOjoReg2.style.display = 'none';
    }

    botonOjoReg2.addEventListener('click', () => {
        const tipoActual = inputPassReg2.getAttribute('type');

        if (tipoActual === 'password') {
            inputPassReg2.setAttribute('type', 'text');
            botonOjoReg2.textContent = 'Ocultar';
        } else {
            inputPassReg2.setAttribute('type', 'password');
            botonOjoReg2.textContent = 'Mostrar';
        }
    });
});