// Variables Vista
const btn_tirar = document.getElementById('btn_tirar');
const img_slot_1 = document.getElementById('slot_1');
const img_slot_2 = document.getElementById('slot_2');
const img_slot_3 = document.getElementById('slot_3');
const span_creditos = document.getElementById('creditos');

//Variables Modelo
let creditos = 100;
let slots = [0,0,0]; //1-Naranja; 2-Limon; 3-Ciruela;4-Campana;5-Siete;

span_creditos.innerText = 100;
btn_tirar.addEventListener('click',tirar);

//FUNCIONES
function tirar(){
    let tirada;
    
    if(creditos < 10)
    {
        alert("Te has quedado sin creditos");
        return;
    }
    creditos -=10;
    
    for(var i=0; i<3;i++)
    {
        tirada = Math.floor(1 + Math.random()*10 % 5);
        slots[i] = tirada;
    }
    //COMPROBAR PREMIO
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
function pintarSlots()
{
    span_creditos.innerText = creditos;

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

    if(slots[0] == 5)
        img_slot_1.setAttribute("src","res/campana.png");
    if(slots[1] == 5)
        img_slot_2.setAttribute("src","res/campana.png");
    if(slots[2] == 5)
        img_slot_3.setAttribute("src","res/campana.png");
}