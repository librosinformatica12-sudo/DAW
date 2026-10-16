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

    if (!formLogin || !formRegistro) return;

    if (nombre === "login") {
        formLogin.classList.remove("oculto");
        formRegistro.classList.add("oculto");
    } else {
        formRegistro.classList.remove("oculto");
        formLogin.classList.add("oculto");
    }

    cambiarHero(nombre);
}

// ============================================================
//    BUSCAR EN MI COLECCIÓN
// ============================================================
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

// ============================================================
//    AL CARGAR LA PÁGINA
// ============================================================
window.onload = function () {
    // Si estamos en index.php, mostramos la pestana inicial
    var contenedor = document.getElementById("contenedor-pestanas");
    if (contenedor) {
        mostrarPestana(contenedor.getAttribute("data-inicial"));
    }
};

// ---------- CAMBIO DE INFO DEL HERO (lado izquierdo) ----------
const heroTitulo = document.getElementById('hero-title');
const heroDescripcion = document.getElementById('hero-description');
const features = document.querySelectorAll('main.pagina .hero ul li');

    const textosHero = {
        login: {
            titulo: 'Bienvenido <span> Accede a tu </span> Cuenta',
            descripcion: 'Introduce tus credenciales para iniciar sesión y disfrutar de todas las funciones de nuestra plataforma.',
            features: [
                { titulo: 'Sobre del Día.', texto: 'Abre tu sobre diario y descubre nuevas cartas para tu colección.' },
                { titulo: 'Colección Personal', texto: 'Filtra tus cartas por rareza, época o estadísticas.' },
                { titulo: 'DinoPedia', texto: 'Revisa la ficha técnica de caad dinosaurio que poses.' }
            ]
        },
        registro: {
            titulo: '¡Regístrate <span>Crea tu Cuenta de </span> Coleccionista!',
            descripcion: 'Regístrate para guardar tu progreso, abrir sobres diarios y completar el catalogo de dinosaurios. ¡Únete a nuestra comunidad de Coleccionistas!',
            features: [
                { titulo: 'Primer Sobre Gratis', texto: 'Empieza tu colección desde el minuto uno.' },
                { titulo: 'Guardado en la Nube', texto: 'No pierdas nunca tus cartas ni tu avance.' },
                { titulo: 'Carálogo Iºnteractivo', texto: 'Accede a toda la información de la DinoPedia.' }
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

// ---------- Avisos y envio con fetch (estilo dino-prueba) ----------
function mostrarAviso(id, texto, ok) {
    var aviso = document.getElementById(id);
    if (!aviso) return;

    aviso.textContent = texto || "";
    aviso.className = "aviso " + (ok ? "ok" : "error");
    aviso.style.display = texto ? "block" : "none";
}

async function enviarFormulario(url, form) {
    var respuesta = await fetch(url, {
        method: "POST",
        body: new FormData(form)
    });

    if (!respuesta.ok) {
        throw new Error("Error en el servidor (" + respuesta.status + ")");
    }

    return await respuesta.json();
}

// ---------- Pestanas con botones (data-ir) ----------
document.querySelectorAll(".btn[data-ir]").forEach(function (boton) {
    boton.addEventListener("click", function () {
        mostrarPestana(boton.getAttribute("data-ir"));
    });
});

// ============================================================
//    LOGIN
// ============================================================
var formLogin = document.getElementById("form-login");
if (formLogin) {
    formLogin.addEventListener("submit", async function (e) {
        e.preventDefault();

        var usuario = document.getElementById("log-usuario").value.trim();
        var clave = document.getElementById("log-pass").value;

        if (!usuario || !clave) {
            mostrarAviso("aviso-login", "Rellena usuario y contraseña.", false);
            return;
        }

        mostrarAviso("aviso-login", "Iniciando sesión...", true);

        try {
            var res = await enviarFormulario("php/login.php", formLogin);

            if (res.ok) {
                mostrarAviso("aviso-login", res.mensaje, true);
                setTimeout(function () {
                    window.location.href = "inicio.php";
                }, 1000);
            } else {
                mostrarAviso("aviso-login", res.mensaje, false);
            }
        } catch (err) {
            mostrarAviso("aviso-login", "No se pudo conectar con el servidor.", false);
        }
    });
}

// ============================================================
//    REGISTRO
// ============================================================
var formRegistro = document.getElementById("form-registro");
if (formRegistro) {
    formRegistro.addEventListener("submit", async function (e) {
        e.preventDefault();

        var usuario = document.getElementById("reg-usuario").value.trim();
        var email = document.getElementById("reg-email").value.trim();
        var clave1 = document.getElementById("reg-pass").value;
        var clave2 = document.getElementById("reg-pass2").value;

        if (!usuario || usuario.length < 3) {
            mostrarAviso("aviso-registro", "El usuario debe tener al menos 3 caracteres.", false);
            return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            mostrarAviso("aviso-registro", "El correo no es válido.", false);
            return;
        }
        if (!clave1 || clave1.length < 4) {
            mostrarAviso("aviso-registro", "La contraseña debe tener al menos 4 caracteres.", false);
            return;
        }
        if (clave1 !== clave2) {
            mostrarAviso("aviso-registro", "Las contraseñas no coinciden.", false);
            return;
        }

        mostrarAviso("aviso-registro", "Procesando registro...", true);

        try {
            var res = await enviarFormulario("php/registro.php", formRegistro);

            if (res.ok) {
                mostrarAviso("aviso-registro", res.mensaje, true);
                formRegistro.reset();

                setTimeout(function () {
                    mostrarPestana("login");
                    mostrarAviso("aviso-login", "Cuenta creada con éxito. Ya puedes acceder.", true);
                }, 1500);
            } else {
                mostrarAviso("aviso-registro", res.mensaje, false);
            }
        } catch (err) {
            mostrarAviso("aviso-registro", "No se pudo completar el registro. Error de red.", false);
        }
    });
}

// ============================================================
//    CONTADORES DE ESPERA DE LOS SOBRES (inicio.php)
//    ------------------------------------------------------------
//    Sobre del día: 24 horas desde la última apertura
//    Sobre de prueba: 2 minutos desde la última apertura
//    Lee data-espera (segundos) y data-label (texto al acabar)
// ============================================================
document.addEventListener("DOMContentLoaded", function () {
    var botones = document.querySelectorAll("[data-espera]");

    botones.forEach(function (boton) {
        var restante = parseInt(boton.getAttribute("data-espera"), 10) || 0;
        if (restante <= 0) return;

        var etiqueta = boton.getAttribute("data-label") || boton.textContent.trim();
        boton.disabled = true;

        function pintar() {
            var h = Math.floor(restante / 3600);
            var m = Math.floor((restante % 3600) / 60);
            var s = restante % 60;
            var texto = (h > 0 ? h + ":" + String(m).padStart(2, "0") : m) +
                ":" + String(s).padStart(2, "0");
            boton.textContent = "En espera (" + texto + ")";
        }

        pintar();

        var id = setInterval(function () {
            restante -= 1;

            if (restante <= 0) {
                clearInterval(id);
                boton.disabled = false;
                boton.textContent = etiqueta;
                return;
            }

            pintar();
        }, 1000);
    });
});



