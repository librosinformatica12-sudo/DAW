// =====================================================
// 1. VISTA
// =====================================================
const contenedorProductos = document.getElementById("productos");
const cuerpoCarrito = document.getElementById("cuerpo-carrito");
const spanTotal = document.getElementById("total");
const pVacio = document.getElementById("vacio");
const btnVaciar = document.getElementById("btn-vaciar");

// =====================================================
// 2. MODELO: catálogo y carrito
// =====================================================
const PRODUCTOS = [
    { id: 1, nombre: "Pan",   precio: 1.20 },
    { id: 2, nombre: "Leche", precio: 0.95 },
    { id: 3, nombre: "Huevos", precio: 2.50 },
    { id: 4, nombre: "Café",  precio: 4.75 }
];

// Cada línea: { id, nombre, precio, cantidad }
let carrito = [];

// =====================================================
// 3. ARRANQUE + EVENTOS
// =====================================================
renderProductos();
renderCarrito();

// Delegación: escuchamos los botones de los productos
contenedorProductos.addEventListener("click", function (evento) {
    const boton = evento.target.closest("button");
    if (!boton) return;

    agregarAlCarrito(Number(boton.dataset.id));
});

// Delegación: escuchamos los botones de quitar del carrito
cuerpoCarrito.addEventListener("click", function (evento) {
    const boton = evento.target.closest("button");
    if (!boton) return;

    quitarDelCarrito(Number(boton.dataset.id));
});

btnVaciar.addEventListener("click", function () {
    carrito = [];
    renderCarrito();
});

// =====================================================
// 4. PINTAR EL CATÁLOGO
// =====================================================
function renderProductos() {
    contenedorProductos.innerHTML = "";

    PRODUCTOS.forEach(function (producto) {
        const articulo = document.createElement("article");
        articulo.className = "producto";

        const titulo = document.createElement("h3");
        titulo.textContent = producto.nombre;

        const precio = document.createElement("p");
        precio.className = "precio";
        precio.textContent = producto.precio.toFixed(2) + " €";

        const boton = document.createElement("button");
        boton.type = "button";
        boton.textContent = "Añadir";
        boton.dataset.id = producto.id;

        articulo.append(titulo, precio, boton);
        contenedorProductos.appendChild(articulo);
    });
}

// =====================================================
// 5. AÑADIR AL CARRITO
// =====================================================
function agregarAlCarrito(id) {
    const producto = PRODUCTOS.find(function (p) {
        return p.id === id;
    });

    if (!producto) return;

    // ¿Ya está en el carrito?
    const linea = carrito.find(function (l) {
        return l.id === id;
    });

    if (linea) {
        linea.cantidad++;
    } else {
        carrito.push({
            id: producto.id,
            nombre: producto.nombre,
            precio: producto.precio,
            cantidad: 1
        });
    }

    renderCarrito();
}

// =====================================================
// 6. QUITAR UNA LÍNEA
// =====================================================
function quitarDelCarrito(id) {
    carrito = carrito.filter(function (linea) {
        return linea.id !== id;
    });

    renderCarrito();
}

// =====================================================
// 7. PINTAR EL CARRITO Y CALCULAR EL TOTAL
// =====================================================
function renderCarrito() {
    cuerpoCarrito.innerHTML = "";

    carrito.forEach(function (linea) {
        const fila = document.createElement("tr");

        const tdNombre = document.createElement("td");
        tdNombre.textContent = linea.nombre;

        const tdPrecio = document.createElement("td");
        tdPrecio.textContent = linea.precio.toFixed(2) + " €";

        const tdCantidad = document.createElement("td");
        tdCantidad.textContent = linea.cantidad;

        const tdSubtotal = document.createElement("td");
        tdSubtotal.textContent = (linea.precio * linea.cantidad).toFixed(2) + " €";

        const tdAccion = document.createElement("td");
        const btnQuitar = document.createElement("button");
        btnQuitar.type = "button";
        btnQuitar.textContent = "Quitar";
        btnQuitar.dataset.id = linea.id;
        tdAccion.appendChild(btnQuitar);

        fila.append(tdNombre, tdPrecio, tdCantidad, tdSubtotal, tdAccion);
        cuerpoCarrito.appendChild(fila);
    });

    // Mostrar u ocultar el mensaje de vacío
    pVacio.style.display = carrito.length === 0 ? "block" : "none";

    // Total
    const total = carrito.reduce(function (acumulado, linea) {
        return acumulado + linea.precio * linea.cantidad;
    }, 0);

    spanTotal.textContent = total.toFixed(2);
}
