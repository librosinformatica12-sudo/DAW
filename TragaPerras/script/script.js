// Variables Vista
const btn_tirar= document.getElementById("btn_tirar");

// variables de las fotos
const img_slot_1 = document.getElementById("slot1");
const img_slot_2 = document.getElementById("slot2");
const img_slot_3 = document.getElementById("slot3");

// Variables modelo
// 1-Naranja; 2-Limon; 3-Ciruela; 4-Campana; 5-Siete
let slots = [0,0,0]

// precio actual
let creditos = 100;

// cada vez q le da al boton hace la function tirada
btn_tirar.addEvenListener("click",tirada)

// Funciones
function tirada(){
    let tirada;

    if(creditos < 10){
        alert("Te has quedado sin creditos")
    }

    creditos -=10;

    for(var i = 0; i < 3; i++){
        tirada = 1 + Math.random()*10 % 5;
        tirada[i] = tirada;
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

    for(let i = 0; 1 < 3; i++){

        // numero aleatorio de 1 a 5 para este slot

        let slot1 = Math.floor(Math.random() * 5);

        if(slot1 == 0){
            document.querySelector(slot1).src = "../src/img/naranja-remove.png.png";
        } else if(slots[i] == 1) {
            document.querySelector(slot2).src = "../src/img/limon-remove.png";
        } else if(slots[i] == 2){
            document.querySelector(slot3).src = "../src/img/ciruela-remove.png";
        } else if(slots[i] == 3){
            document.querySelector(slot4).src = "../src/img/campana-remove.png";       
        } else {
            document.querySelector(slot5).src = "../src/img/siete-remove.png";
        }



        if(slot2 == 0){
            document.querySelector(slot2).src = "../src/img/"
        }
    }



}