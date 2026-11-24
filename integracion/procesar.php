<?php
// 1. Iniciamos la sesión para poder acceder a $_SESSION
session_start();

// Indicamos al navegador que la respuesta de este script será de tipo JSON
header('Content-Type: application/json; charset=utf-8');

// 2. Comprobamos si hemos recibido el dato desde JavaScript por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['usuario'])) {
    
    $nombreRecibido = $_POST['usuario'];

    // 3. Guardamos el dato en la SESIÓN del servidor
    $_SESSION['usuario_actual'] = $nombreRecibido;

    // 4. Preparamos la respuesta que devolveremos a JavaScript
    $respuesta = [
        'status'          => 'exito',
        'mensaje'         => "Servidor PHP: Se recibió correctamente '$nombreRecibido' a través de \$_POST.",
        'sesion_guardada' => $_SESSION['usuario_actual']
    ];

} else {
    // Respuesta de error si no se enviaron datos válidos
    $respuesta = [
        'status'  => 'error',
        'mensaje' => 'No se recibieron datos por POST.'
    ];
}

// 5. Convertimos el array de PHP a formato JSON y lo enviamos como respuesta
echo json_encode($respuesta);