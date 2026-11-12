/* ============================================================
   tragaperras.js (TragaperrasV1)
   ÍNDICE RÁPIDO (Ctrl+F):
     REFERENCIAS DOM
     VARIABLES MODELO
     INICIALIZACION
     tirar
     COMPROBAR PREMIO
     pintarSlots
     MAPA DE SIMBOLOS
   ============================================================ */

// ===== REFERENCIAS DOM =====
// Variables Vista
const btn_tirar = document.getElementById('btn_tirar');
const img_slot_1 = document.getElementById('slot_1');
const img_slot_2 = document.getElementById('slot_2');
const img_slot_3 = document.getElementById('slot_3');
const span_creditos = document.getElementById('creditos');

// ===== VARIABLES MODELO =====
// creditos = saldo actual; slots = los 3 números de la tirada.
let creditos = 100;
let slots = [0,0,0]; //1-Naranja; 2-Limon; 3-Ciruela;4-Campana;5-Siete;

// ===== INICIALIZACION =====
// Se pinta el saldo inicial y se engancha el botón a la función tirar.
span_creditos.innerText = 100;
btn_tirar.addEventListener('click',tirar);

// ===== tirar =====
// 1) Comprueba saldo. 2) Cobra 10 créditos. 3) Genera 3 símbolos.
// 4) Comprueba premio. 5) Pinta los slots.
function tirar(){
    let tirada;
    
    if(creditos < 10)
    {
        alert("Te has quedado sin creditos");
        return;
    }
    creditos -=10;
    
    // Genera un número aleatorio del 1 al 5 para cada slot.
    for(var i=0; i<3;i++)
    {
        tirada = Math.floor(1 + Math.random()*10 % 5);
        slots[i] = tirada;
    }

    // ===== COMPROBAR PREMIO =====
    // Solo hay premio si los 3 slots son iguales.
    if(slots[0] == slots[1] && slots[1] == slots[2])
    {
        if(slots[0] == 1)
            creditos+=5;
        if(slots[0] == 2)
            creditos+=7;
        if(slots[0] == 3)
            creditos+=10;
        if(slots[0] == 4)
            creditos+=20;
        if(slots[0] == 5)
            creditos+=50;
    }

    pintarSlots();
}
// ===== pintarSlots =====
// Actualiza el saldo y cambia la imagen de cada slot según su número.
function pintarSlots()
{
    span_creditos.innerText = creditos;

    // ===== MAPA DE SIMBOLOS =====
    // 1 = naranja, 2 = limón, 3 = ciruela, 4 = campana, 5 = siete.
    if(slots[0] == 1)
        img_slot_1.setAttribute("src","res/naranja.png");
    if(slots[1] == 1)
        img_slot_2.setAttribute("src","res/naranja.png");
    if(slots[2] == 1)
        img_slot_3.setAttribute("src","res/naranja.png");

    if(slots[0] == 2)
        img_slot_1.setAttribute("src","res/limon.png");
    if(slots[1] == 2)
        img_slot_2.setAttribute("src","res/limon.png");
    if(slots[2] == 2)
        img_slot_3.setAttribute("src","res/limon.png");

    if(slots[0] == 3)
        img_slot_1.setAttribute("src","res/ciruela.png");
    if(slots[1] == 3)
        img_slot_2.setAttribute("src","res/ciruela.png");
    if(slots[2] == 3)
        img_slot_3.setAttribute("src","res/ciruela.png");

    if(slots[0] == 4)
        img_slot_1.setAttribute("src","res/campana.png");
    if(slots[1] == 4)
        img_slot_2.setAttribute("src","res/campana.png");
    if(slots[2] == 4)
        img_slot_3.setAttribute("src","res/campana.png");

    // OJO: el 5 es el siete, pero aquí se está usando campana.png.
    if(slots[0] == 5)
        img_slot_1.setAttribute("src","res/campana.png");
    if(slots[1] == 5)
        img_slot_2.setAttribute("src","res/campana.png");
    if(slots[2] == 5)
        img_slot_3.setAttribute("src","res/campana.png");
}