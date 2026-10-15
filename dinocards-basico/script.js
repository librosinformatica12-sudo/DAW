/* ============================================================
   script.js - Funciones sencillas de las paginas
   ============================================================ */

// ---------- Mostrar u ocultar la contraseña ----------
function alternarClave(idInput, boton) {
    var input = document.getElementById(idInput);

    if (input.type === "password") {
        input.type = "text";
        boton.textContent = "Ocultar";
    } else {
        input.type = "password";
        boton.textContent = "Mostrar";
    }
}

// ---------- Cambiar entre login y registro (index.php) ----------
function mostrarPestana(nombre) {
    var formLogin = document.getElementById("form-login");
    var formRegistro = document.getElementById("form-registro");
    var btnLogin = document.getElementById("btn-login");
    var btnRegistro = document.getElementById("btn-registro");

    if (nombre === "login") {
        formLogin.classList.remove("oculto");
        formRegistro.classList.add("oculto");
        btnLogin.classList.add("activa");
        btnRegistro.classList.remove("activa");
    } else {
        formRegistro.classList.remove("oculto");
        formLogin.classList.add("oculto");
        btnRegistro.classList.add("activa");
        btnLogin.classList.remove("activa");
    }
}

// ---------- Buscar dentro de mi coleccion ----------
function filtrarColeccion() {
    var texto = document.getElementById("buscador").value.toLowerCase();
    var cartas = document.getElementsByClassName("carta-mini");

    for (var i = 0; i < cartas.length; i++) {
        var contenido = cartas[i].getAttribute("data-buscar");

        if (contenido.indexOf(texto) !== -1) {
            cartas[i].style.display = "";
        } else {
            cartas[i].style.display = "none";
        }
    }
}

// ---------- Al cargar la pagina ----------
window.onload = function () {
    // Si estamos en index.php, mostramos la pestana inicial
    var contenedor = document.getElementById("contenedor-pestanas");
    if (contenedor) {
        mostrarPestana(contenedor.getAttribute("data-inicial"));
    }
};
