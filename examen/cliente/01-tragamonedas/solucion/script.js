// =====================================================
// 1. VISTA: referencias a los elementos del HTML
// =====================================================
const btnTirar     = document.getElementById("btn_tirar");
const btnReiniciar = document.getElementById("btn_reiniciar");
const spanCreditos = document.getElementById("creditos");
const pMensaje     = document.getElementById("mensaje");
const slotsVista   = [
    document.getElementById("slot1"),
    document.getElementById("slot2"),
    document.getElementById("slot3")
];

// =====================================================
// 2. MODELO: datos del juego (no toca el HTML)
// =====================================================
// 1 = naranja, 2 = limón, 3 = cereza, 4 = campana, 5 = siete
const SIMBOLOS = {
    1: "🍊",
    2: "🍋",
    3: "🍒",
    4: "🔔",
    5: "7"
};

// PREMIOS: si salen tres iguales, cuánto se gana
const PREMIOS = {
    1: 5,
    2: 7,
    3: 10,
    4: 20,
    5: 50
};

let slots = [5, 5, 5];   // lo que ha salido en cada rodillo
let creditos = 100;      // créditos disponibles

// =====================================================
// 3. ARRANQUE: pintar el estado inicial y escuchar clics
// =====================================================
pintarSlots();

btnTirar.addEventListener("click", tirada);
btnReiniciar.addEventListener("click", reiniciar);

// =====================================================
// 4. FUNCIÓN PRINCIPAL: una tirada
// =====================================================
function tirada() {
    // 4.1 Validar: si no hay créditos, avisar y salir
    if (creditos < 10) {
        pMensaje.textContent = "Te has quedado sin créditos. Pulsa Reiniciar.";
        return; // corta la función
    }

    // 4.2 Cobrar la tirada
    creditos -= 10;

    // 4.3 Generar tres números aleatorios del 1 al 5
    for (let i = 0; i < 3; i++) {
        slots[i] = Math.floor(Math.random() * 5) + 1;
    }

    // 4.4 Comprobar premio: los tres iguales
    if (slots[0] === slots[1] && slots[1] === slots[2]) {
        const premio = PREMIOS[slots[0]];
        creditos += premio;
        pMensaje.textContent = "¡TRIPLE! Has ganado " + premio + " créditos.";
    } else {
        pMensaje.textContent = "No ha habido suerte. Prueba otra vez.";
    }

    // 4.5 Actualizar la pantalla
    pintarSlots();
}

// =====================================================
// 5. FUNCIÓN DE PINTADO: modelo -> HTML
// =====================================================
function pintarSlots() {
    spanCreditos.textContent = creditos;

    for (let i = 0; i < 3; i++) {
        slotsVista[i].textContent = SIMBOLOS[slots[i]];

        // Marcar en verde si los tres son iguales
        if (slots[0] === slots[1] && slots[1] === slots[2]) {
            slotsVista[i].classList.add("ganador");
        } else {
            slotsVista[i].classList.remove("ganador");
        }
    }
}

// =====================================================
// 6. EXTRA: reiniciar la partida
// =====================================================
function reiniciar() {
    creditos = 100;
    slots = [5, 5, 5];
    pMensaje.textContent = "Partida reiniciada.";
    pintarSlots();
}
