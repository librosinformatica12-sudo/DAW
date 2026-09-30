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