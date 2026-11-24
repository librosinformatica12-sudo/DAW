document.getElementById('btnEnviar').addEventListener('click', function() {
    // 1. Capturamos el valor del campo de texto
    const nombreIngresado = document.getElementById('nombre').value;

    if (nombreIngresado.trim() === '') {
        alert('Por favor, ingresa un nombre.');
        return;
    }

    // 2. Empaquetamos los datos en un objeto FormData (PHP lo recibirá en $_POST)
    const datos = new FormData();
    datos.append('usuario', nombreIngresado);

    // 3. Realizamos la llamada asíncrona a procesar.php mediante POST
    fetch('procesar.php', {
        method: 'POST',
        body: datos
    })
    .then(response => response.json()) // Convertimos la respuesta JSON de PHP a un objeto JS
    .then(data => {
        // 4. Mostramos en pantalla los datos devueltos por el servidor
        document.getElementById('resultado').innerHTML =
            `<p>${data.mensaje}</p>
             <p>Dato guardado en la sesión del servidor: <strong>${data.sesion_guardada}</strong></p>`;
    })
    .catch(error => {
        console.error('Error en la petición:', error);
        document.getElementById('resultado').innerText = 'Ocurrió un error al procesar la petición.';
    });
});
