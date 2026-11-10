// =====================================================
// 1. VISTA: elementos del HTML
// =====================================================
const form         = document.getElementById("form-tarea");
const input        = document.getElementById("input-tarea");
const lista        = document.getElementById("lista");
const contador     = document.getElementById("contador");
const mensaje      = document.getElementById("mensaje");
const btnBorrarHechas = document.getElementById("btn-borrar-hechas");

// =====================================================
// 2. MODELO: array de tareas
// Cada tarea es un objeto: { id, texto, hecha }
// =====================================================
let tareas = [];
let siguienteId = 1;

// =====================================================
// 3. EVENTOS
// =====================================================
form.addEventListener("submit", agregarTarea);

// Delegación: un solo listener en la lista escucha los botones hijos
lista.addEventListener("click", manejarClic);

btnBorrarHechas.addEventListener("click", borrarHechas);

// =====================================================
// 4. AÑADIR TAREA
// =====================================================
function agregarTarea(evento) {
    evento.preventDefault(); // evita que el formulario recargue la página

    const texto = input.value.trim();

    if (texto === "") {
        mensaje.textContent = "Escribe algo antes de añadir.";
        return;
    }

    tareas.push({
        id: siguienteId,
        texto: texto,
        hecha: false
    });

    siguienteId++;
    input.value = "";
    input.focus();
    mensaje.textContent = "";
    render();
}

// =====================================================
// 5. GESTIONAR CLICS DE LOS BOTONES DE CADA TAREA
// =====================================================
function manejarClic(evento) {
    const boton = evento.target.closest("button");
    if (!boton) return; // el clic no fue en un botón

    const id = Number(boton.dataset.id);
    const accion = boton.dataset.accion;

    if (accion === "alternar") {
        const tarea = tareas.find(function (t) {
            return t.id === id;
        });
        if (tarea) tarea.hecha = !tarea.hecha;
    }

    if (accion === "borrar") {
        tareas = tareas.filter(function (t) {
            return t.id !== id;
        });
    }

    render();
}

// =====================================================
// 6. BORRAR TODAS LAS COMPLETADAS
// =====================================================
function borrarHechas() {
    const antes = tareas.length;

    tareas = tareas.filter(function (t) {
        return !t.hecha;
    });

    if (tareas.length === antes) {
        mensaje.textContent = "No hay tareas completadas.";
    } else {
        mensaje.textContent = "Tareas completadas eliminadas.";
    }

    render();
}

// =====================================================
// 7. RENDER: modelo -> HTML
// =====================================================
function render() {
    // Vaciar la lista antes de volver a pintarla
    lista.innerHTML = "";

    tareas.forEach(function (tarea) {
        const li = document.createElement("li");
        if (tarea.hecha) li.classList.add("hecha");

        const span = document.createElement("span");
        span.textContent = tarea.texto;

        const btnAlternar = document.createElement("button");
        btnAlternar.type = "button";
        btnAlternar.textContent = tarea.hecha ? "Deshacer" : "Hecha";
        btnAlternar.dataset.id = tarea.id;
        btnAlternar.dataset.accion = "alternar";

        const btnBorrar = document.createElement("button");
        btnBorrar.type = "button";
        btnBorrar.textContent = "Borrar";
        btnBorrar.className = "secundario";
        btnBorrar.dataset.id = tarea.id;
        btnBorrar.dataset.accion = "borrar";

        li.append(span, btnAlternar, btnBorrar);
        lista.appendChild(li);
    });

    // Contador
    const pendientes = tareas.filter(function (t) {
        return !t.hecha;
    }).length;

    contador.textContent =
        tareas.length + " tareas (" + pendientes + " pendientes)";
}
