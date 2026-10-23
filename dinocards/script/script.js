/* ============================================================
   script.js - Funciones sencillas de las paginas
   ------------------------------------------------------------
   Este archivo se encarga de:
     1. Mostrar/ocultar la contraseña
     2. Cambiar entre login y registro (pestañas)
     3. Buscar cartas en la colección
     4. Cambiar los textos del lado izquierdo (hero)
     5. Enviar los formularios de login y registro con fetch
     6. Contadores de espera de los sobres
   ============================================================ */


// ============================================================
//    MOSTRAR U OCULTAR LA CONTRASEÑA
// ============================================================
/*
   alternarClave(idInput, boton)
   - idInput: el id del <input> de la contraseña (ej: "log-pass")
   - boton:   el propio botón que se ha pulsado (se pasa con "this")
   Si el input es de tipo "password" lo cambia a "text" para que se
   vea la clave, y viceversa. También cambia el texto del botón.
*/
function alternarClave(idInput, boton) {
    // Buscamos el input en la página por su id
    var input = document.getElementById(idInput);

    if (input.type === "password") {
        // Estaba oculta -> la mostramos
        input.type = "text";
        boton.textContent = "Ocultar";
    } else {
        // Estaba visible -> la ocultamos
        input.type = "password";
        boton.textContent = "Mostrar";
    }
}


// ============================================================
//    CAMBIAR ENTRE LOGIN Y REGISTRO (index.php)
// ============================================================
/*
   mostrarPestana(nombre)
   - nombre: "login" o "registro"
   Muestra un formulario y oculta el otro usando la clase CSS "oculto".
   Además llama a cambiarHero() para actualizar los textos del lado izquierdo.
*/
function mostrarPestana(nombre) {
    var formLogin = document.getElementById("form-login");
    var formRegistro = document.getElementById("form-registro");

    // Si esta página no tiene los formularios (ej: inicio.php), salimos
    // para evitar errores de JavaScript
    if (!formLogin || !formRegistro) return;

    if (nombre === "login") {
        formLogin.classList.remove("oculto");   // mostramos el login
        formRegistro.classList.add("oculto");   // ocultamos el registro
    } else {
        formRegistro.classList.remove("oculto"); // mostramos el registro
        formLogin.classList.add("oculto");       // ocultamos el login
    }

    // Actualizamos el título, descripción y características del hero
    cambiarHero(nombre);
}


// ============================================================
//    BUSCAR EN MI COLECCIÓN
// ============================================================
/*
   filtrarColeccion()
   Se llama cada vez que el usuario escribe en el buscador.
   Recorre todas las cartas (clase "carta-mini") y compara el texto
   escrito con su atributo data-buscar. Si coincide, la muestra;
   si no, la oculta.
*/
function filtrarColeccion() {
    // Texto del buscador en minúsculas para no distinguir mayúsculas
    var texto = document.getElementById("buscador").value.toLowerCase();

    // Todas las cartas de la colección
    var cartas = document.getElementsByClassName("carta-mini");

    for (var i = 0; i < cartas.length; i++) {
        // data-buscar contiene el texto por el que se puede buscar la carta
        var contenido = cartas[i].getAttribute("data-buscar");

        // indexOf devuelve -1 si el texto NO aparece dentro del contenido
        if (contenido.indexOf(texto) !== -1) {
            cartas[i].style.display = "";      // se muestra (valor por defecto)
        } else {
            cartas[i].style.display = "none";  // se oculta
        }
    }
}


// ============================================================
//    AL CARGAR LA PÁGINA
// ============================================================
/*
   window.onload se ejecuta cuando la página ha terminado de cargar.
   Si existe el elemento "contenedor-pestanas" (solo en index.php),
   mostramos la pestaña que indique su atributo data-inicial
   (por ejemplo "login" o "registro").
*/
window.onload = function () {
    var contenedor = document.getElementById("contenedor-pestanas");
    if (contenedor) {
        mostrarPestana(contenedor.getAttribute("data-inicial"));
    }
};


// ============================================================
//    CAMBIO DE INFO DEL HERO (lado izquierdo)
// ============================================================

// Elementos de la página que vamos a modificar
const heroTitulo = document.getElementById('hero-title');
const heroDescripcion = document.getElementById('hero-description');
// Lista de las 3 características (<li>) que aparecen en el hero
const features = document.querySelectorAll('main.pagina .hero ul li');

/*
   textosHero: objeto con los textos de cada modo.
   Cada modo (login / registro) tiene:
     - titulo:      título principal (admite HTML, por eso lleva <span>)
     - descripcion: párrafo explicativo
     - features:    3 características, cada una con titulo y texto
*/
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

/*
   cambiarHero(modo)
   - modo: "login" o "registro"
   Coge los textos de ese modo desde textosHero y los pinta en el hero.
*/
function cambiarHero(modo) {
    // Textos correspondientes al modo recibido
    const t = textosHero[modo];

    // Si el modo no existe o faltan elementos en la página, no hacemos nada
    if (!t || !heroTitulo || !heroDescripcion) return;

    // innerHTML porque el título lleva etiquetas <span>
    heroTitulo.innerHTML = t.titulo;
    // textContent porque la descripción es texto plano
    heroDescripcion.textContent = t.descripcion;

    // Recorremos cada <li> y le ponemos su título (<b>) y texto (<small>)
    features.forEach((li, i) => {
        if (!t.features[i]) return; // si no hay texto para este <li>, lo saltamos
        li.querySelector('b').textContent = t.features[i].titulo;
        li.querySelector('small').textContent = t.features[i].texto;
    });
}


// ============================================================
//    AVISOS Y ENVÍO CON FETCH
// ============================================================
/*
   mostrarAviso(id, texto, ok)
   - id:    id del elemento donde se muestra el mensaje
   - texto: mensaje a mostrar (si está vacío, se oculta el aviso)
   - ok:    true = mensaje de éxito (clase "ok"), false = error (clase "error")
*/
function mostrarAviso(id, texto, ok) {
    var aviso = document.getElementById(id);
    if (!aviso) return; // si el elemento no existe, salimos

    aviso.textContent = texto || "";
    // Ponemos la clase "aviso ok" o "aviso error" para que el CSS le dé color
    aviso.className = "aviso " + (ok ? "ok" : "error");
    // Solo se ve si hay texto
    aviso.style.display = texto ? "block" : "none";
}

/*
   enviarFormulario(url, form)
   Envía los datos de un formulario a un archivo PHP usando fetch (AJAX),
   sin recargar la página.
   - url:  archivo PHP al que se envía (ej: "php/login.php")
   - form: el formulario HTML del que se cogen los datos
   Devuelve el JSON que responde el PHP (ej: { ok: true, mensaje: "..." }).
   Es "async" porque fetch tarda en responder y usamos "await" para esperar.
*/
async function enviarFormulario(url, form) {
    var respuesta = await fetch(url, {
        method: "POST",
        body: new FormData(form) // FormData recoge todos los campos del formulario
    });

    // Si el servidor responde con error (404, 500...) lanzamos una excepción
    if (!respuesta.ok) {
        throw new Error("Error en el servidor (" + respuesta.status + ")");
    }

    // Convertimos la respuesta de texto JSON a un objeto JavaScript
    return await respuesta.json();
}


// ============================================================
//    PESTAÑAS CON BOTONES (data-ir)
// ============================================================
/*
   Cada botón con clase "btn" y atributo data-ir (ej: data-ir="registro")
   cambia de pestaña al hacer clic, usando mostrarPestana().
*/
document.querySelectorAll(".btn[data-ir]").forEach(function (boton) {
    boton.addEventListener("click", function () {
        mostrarPestana(boton.getAttribute("data-ir"));
    });
});


// ============================================================
//    LOGIN
// ============================================================
var formLogin = document.getElementById("form-login");

// Solo si el formulario existe en esta página
if (formLogin) {
    // Escuchamos el evento "submit" (cuando se pulsa enviar)
    formLogin.addEventListener("submit", async function (e) {
        // Evitamos que el formulario recargue la página
        e.preventDefault();

        // Leemos los valores (trim() quita espacios al principio y final)
        var usuario = document.getElementById("log-usuario").value.trim();
        var clave = document.getElementById("log-pass").value;

        // Validación básica: ningún campo puede estar vacío
        if (!usuario || !clave) {
            mostrarAviso("aviso-login", "Rellena usuario y contraseña.", false);
            return;
        }

        mostrarAviso("aviso-login", "Iniciando sesión...", true);

        try {
            // Enviamos los datos a php/login.php y esperamos la respuesta
            var res = await enviarFormulario("php/login.php", formLogin);

            if (res.ok) {
                // Login correcto: mostramos mensaje y vamos a inicio.php tras 1 segundo
                mostrarAviso("aviso-login", res.mensaje, true);
                setTimeout(function () {
                    window.location.href = "inicio.php";
                }, 1000);
            } else {
                // Login incorrecto: mostramos el mensaje de error del servidor
                mostrarAviso("aviso-login", res.mensaje, false);
            }
        } catch (err) {
            // Fallo de red o del servidor
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
        e.preventDefault(); // no recargar la página

        // Leemos los datos del formulario
        var usuario = document.getElementById("reg-usuario").value.trim();
        var email = document.getElementById("reg-email").value.trim();
        var clave1 = document.getElementById("reg-pass").value;
        var clave2 = document.getElementById("reg-pass2").value;

        // --- Validaciones en el navegador (antes de enviar al servidor) ---

        // Usuario: mínimo 3 caracteres
        if (!usuario || usuario.length < 3) {
            mostrarAviso("aviso-registro", "El usuario debe tener al menos 3 caracteres.", false);
            return;
        }
        // Email: expresión regular que comprueba el formato algo@algo.algo
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            mostrarAviso("aviso-registro", "El correo no es válido.", false);
            return;
        }
        // Contraseña: mínimo 4 caracteres
        if (!clave1 || clave1.length < 4) {
            mostrarAviso("aviso-registro", "La contraseña debe tener al menos 4 caracteres.", false);
            return;
        }
        // Las dos contraseñas deben ser iguales
        if (clave1 !== clave2) {
            mostrarAviso("aviso-registro", "Las contraseñas no coinciden.", false);
            return;
        }

        mostrarAviso("aviso-registro", "Procesando registro...", true);

        try {
            // Enviamos los datos a php/registro.php
            var res = await enviarFormulario("php/registro.php", formRegistro);

            if (res.ok) {
                mostrarAviso("aviso-registro", res.mensaje, true);
                formRegistro.reset(); // vaciamos el formulario

                // Tras 1,5 segundos pasamos a la pestaña de login con un aviso de éxito
                setTimeout(function () {
                    mostrarPestana("login");
                    mostrarAviso("aviso-login", "Cuenta creada con éxito. Ya puedes acceder.", true);
                }, 1500);
            } else {
                // El servidor rechazó el registro (ej: usuario ya existe)
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
/*
   DOMContentLoaded se dispara cuando el HTML ya está listo.
   Buscamos todos los botones con atributo data-espera. Si el PHP
   les ha puesto segundos pendientes, los desactivamos y mostramos
   una cuenta atrás hasta que se pueda volver a abrir el sobre.
*/
document.addEventListener("DOMContentLoaded", function () {
    var botones = document.querySelectorAll("[data-espera]");

    botones.forEach(function (boton) {
        // Segundos que faltan (si no es un número válido, usamos 0)
        var restante = parseInt(boton.getAttribute("data-espera"), 10) || 0;

        // Si no hay que esperar, dejamos el botón como está
        if (restante <= 0) return;

        // Texto original del botón, para restaurarlo cuando termine la espera
        var etiqueta = boton.getAttribute("data-label") || boton.textContent.trim();
        boton.disabled = true; // bloqueamos el botón

        /*
           pintar()
           Convierte los segundos restantes a formato h:mm:ss (o m:ss si
           no llega a una hora) y lo escribe dentro del botón.
        */
        function pintar() {
            var h = Math.floor(restante / 3600);          // horas completas
            var m = Math.floor((restante % 3600) / 60);   // minutos restantes
            var s = restante % 60;                        // segundos restantes

            // Si hay horas: "h:mm"; si no: solo "m". Los minutos llevan
            // un 0 delante cuando hay horas (padStart(2, "0"))
            var texto = (h > 0 ? h + ":" + String(m).padStart(2, "0") : m) +
                ":" + String(s).padStart(2, "0");

            boton.textContent = "En espera (" + texto + ")";
        }

        pintar(); // pintamos el valor inicial sin esperar 1 segundo

        // Cada 1000 ms (1 segundo) restamos uno y actualizamos el texto
        var id = setInterval(function () {
            restante -= 1;

            if (restante <= 0) {
                // Se acabó la espera: paramos el reloj y reactivamos el botón
                clearInterval(id);
                boton.disabled = false;
                boton.textContent = etiqueta;
                return;
            }

            pintar();
        }, 1000);
    });
});