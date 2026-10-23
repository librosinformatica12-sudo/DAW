// Declaraciones: referencias a los elementos del HTML.
const titulo = document.getElementById("titulo");
const botonTitulo = document.getElementById("boton-titulo");
const botonMensaje = document.getElementById("boton-mensaje");
const primerMensaje = document.querySelector(".mensaje");
const botonTodos = document.getElementById("boton-todos");
const elementos = document.querySelectorAll(".elemento");
const botonClase = document.getElementById("boton-clase");
const tarjetas = document.getElementsByClassName("tarjeta");
const botonEtiqueta = document.getElementById("boton-etiqueta");
const elementosDeLista = document.getElementsByTagName("li");

// Función declarada por separado, asignada al evento por su nombre.
function cambiarTitulo() {
  titulo.textContent = "¡JavaScript cambió este título!";
}

botonTitulo.addEventListener("click", cambiarTitulo);

// querySelector devuelve solo el primer elemento que coincide.
botonMensaje.addEventListener("click", function () {
  primerMensaje.textContent = "¡El primer mensaje ha cambiado!";
  primerMensaje.style.color = "#25804a";
});

// querySelectorAll devuelve todos los elementos que coinciden.
botonTodos.addEventListener("click", function () {
  elementos.forEach(function (elemento) {
    elemento.classList.add("resaltado");
  });
});

// getElementsByClassName encuentra los elementos que comparten una clase.
botonClase.addEventListener("click", function () {
  for (const tarjeta of tarjetas) {
    tarjeta.style.borderColor = "#5268c5";
  }
});

// getElementsByTagName encuentra los elementos con esa etiqueta HTML.
botonEtiqueta.addEventListener("click", function () {
  for (const elemento of elementosDeLista) {
    elemento.style.fontWeight = "bold";
  }
});
