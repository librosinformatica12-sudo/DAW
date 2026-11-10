// =====================================================
// 1. VISTA
// =====================================================
const form       = document.getElementById("form-intento");
const input      = document.getElementById("numero");
const btnProbar  = document.getElementById("btn-probar");
const btnReiniciar = document.getElementById("btn-reiniciar");
const pMensaje   = document.getElementById("mensaje");
const spanIntentos = document.getElementById("intentos");
const historial  = document.getElementById("historial");

// =====================================================
// 2. MODELO
// =====================================================
let secreto = generarNumero();
let intentos = 0;
let terminado = false;

// =====================================================
// 3. EVENTOS
// =====================================================
form.addEventListener("submit", function (evento) {
    evento.preventDefault();
    comprobarIntento();
});

btnReiniciar.addEventListener("click", reiniciar);

// =====================================================
// 4. GENERAR NÚMERO SECRETO (1..100)
// =====================================================
function generarNumero() {
    return Math.floor(Math.random() * 100) + 1;
}

// =====================================================
// 5. COMPROBAR EL INTENTO
// =====================================================
function comprobarIntento() {
    if (terminado) return;

    const texto = input.value.trim();
    const valor = Number(texto);

    // 5.1 Validar: vacío, no entero o fuera de rango
    if (texto === "" || !Number.isInteger(valor) || valor < 1 || valor > 100) {
        pMensaje.textContent = "Escribe un número entero entre 1 y 100.";
        pMensaje.className = "mensaje error";
        return;
    }

    // 5.2 Contar el intento
    intentos++;
    spanIntentos.textContent = intentos;

    // 5.3 Comparar con el secreto
    if (valor === secreto) {
        pMensaje.textContent = "¡ACERTASTE! Era el " + secreto + ".";
        pMensaje.className = "mensaje acierto";
        anadirHistorial(valor, "acierto");
        terminarPartida();
    } else if (valor < secreto) {
        pMensaje.textContent = "El número secreto es MÁS ALTO.";
        pMensaje.className = "mensaje bajo";
        anadirHistorial(valor, "bajo");
        limpiarInput();
    } else {
        pMensaje.textContent = "El número secreto es MÁS BAJO.";
        pMensaje.className = "mensaje alto";
        anadirHistorial(valor, "alto");
        limpiarInput();
    }
}

// =====================================================
// 6. HISTORIAL
// =====================================================
function anadirHistorial(valor, tipo) {
    const li = document.createElement("li");

    if (tipo === "acierto") {
        li.textContent = valor + " → ¡correcto!";
        li.classList.add("acierto");
    } else if (tipo === "bajo") {
        li.textContent = valor + " → más alto";
    } else {
        li.textContent = valor + " → más bajo";
    }

    historial.appendChild(li);
}

// =====================================================
// 7. TERMINAR / REINICIAR
// =====================================================
function terminarPartida() {
    terminado = true;
    input.disabled = true;
    btnProbar.disabled = true;
}

function reiniciar() {
    secreto = generarNumero();
    intentos = 0;
    terminado = false;

    spanIntentos.textContent = "0";
    historial.innerHTML = "";
    input.disabled = false;
    btnProbar.disabled = false;
    input.value = "";

    pMensaje.textContent = "Nueva partida. ¡Suerte!";
    pMensaje.className = "mensaje";
    input.focus();
}

function limpiarInput() {
    input.value = "";
    input.focus();
}
