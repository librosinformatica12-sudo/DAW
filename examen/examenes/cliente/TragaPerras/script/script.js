// Variables Vista
const btn_tirar= document.getElementById("btn_tirar_rule");

// variables de las fotos
const img_slot_1 = document.getElementById("slot1");
const img_slot_2 = document.getElementById("slot2");
const img_slot_3 = document.getElementById("slot3");

const span_creditos = document.getElementById("creditos");

// Variables modelo
// 1-Naranja; 2-Limon; 3-Ciruela; 4-Campana; 5-Siete
let slots = [0,0,0];

// precio actual
let creditos = 100;

const IMAGENES = {
    1: "src/naranja-remove.png",
    2: "src/limon-remove.png",
    3: "src/ciruela-remove.png",
    4: "src/campana-remove.png",
    5: "src/siete-remove.png"
};

span_creditos.innerText = creditos;

// cada vez q le da al boton hace la function tirada
btn_tirar.addEventListener("click", tirada);

// Funciones
function tirada(){
    if(creditos < 10){
        alert("Te has quedado sin creditos");
        return;
    }

    creditos -= 10;

    for(let i = 0; i < 3; i++){
        slots[i] = Math.floor(Math.random() * 5) + 1;
    }

    // comprobar premio
    if(slots[0] === slots[1] && slots[1] === slots[2]){
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


function pintarSlots(){

    span_creditos.innerText = creditos;

    img_slot_1.setAttribute("src", IMAGENES[slots[0]]);
    img_slot_2.setAttribute("src", IMAGENES[slots[1]]);
    img_slot_3.setAttribute("src", IMAGENES[slots[2]]);
}