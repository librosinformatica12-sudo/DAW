<?php

session_start();
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/AccesoDatos.php';

function iniciarSesion()
{
    $email = $_POST['email'] ?? '';
    $contrasena = $_POST['password'] ?? '';

    try
    {
        $login = PA_Login($email, $contrasena);
        if ($login['resultado'] !== 0)
        {
            $resultado = -99;
            $respuesta = [
            'status'          => 'error',
            'mensaje'         => "Servidor PHP: Login incorrecto",
            'resultado'       => $resultado
            ];
            echo json_encode($respuesta);
            exit;
        }

        $usuario = $login['usuario'];
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id' => $usuario['id'],
            'username' => $usuario['username'] ?? $usuario['nick'] ?? null,
            'email' => $usuario['email'] ?? trim($email),
            'fechaHoraUltimaObtencionCarta' => $usuario['ultima_obtencion'] ?? null,
            'listo_negociar' => $usuario['listo_negociar'] ?? null
        ];

        $_SESSION['coleccion'] = PA_ObtenerColeccion($usuario['id']);

        $resultado = 0;

        $respuesta = [
        'status'          => 'exito',
        'mensaje'         => "Servidor PHP: Login Correcto",
        'resultado'       => $resultado,
        'usuario'         => $_SESSION['usuario'],
        'coleccion'       => $_SESSION['coleccion']
        ];
        echo json_encode($respuesta);

        exit;
    } catch (InvalidArgumentException $exception) {
       $resultado = -99;
        echo json_encode($resultado);
    } catch (Throwable $exception) {
        $resultado = -99;
        echo json_encode($resultado);
    }
}

function registrarUsuario()
{
    $nick = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $contrasena = $_POST['password'] ?? '';
    $confirmacion = $_POST['confirmPassword'] ?? '';

    if (!is_string($nick) || !is_string($email) || !is_string($contrasena) || !is_string($confirmacion)) {
        $resultado = -1;
         $respuesta = [
        'status'          => 'error',
        'mensaje'         => "Servidor PHP: Se detecto algún error en los campos",
        'resultado'       => $resultado
        ];
        echo json_encode($respuesta);
        exit;
    }

    if ($contrasena !== $confirmacion) {
        $resultado = -3;
         $respuesta = [
        'status'          => 'error',
        'mensaje'         => "Servidor PHP: las contraseñas no coinciden",
        'resultado'       => $resultado
        ];
        echo json_encode($respuesta);
        exit;
    }
    try {
        $resultado = PA_Registrar($nick, $email, $contrasena);
        $respuesta = [
        'status'          => 'exito',
        'mensaje'         => "Servidor PHP: Se recibió correctamente los datos a través de \$_POST.",
        'resultado'       => $resultado
        ];
        echo json_encode($respuesta);

    } catch (InvalidArgumentException $exception) {
        $resultado = -99;
        echo json_encode($resultado);
    } catch (Throwable $exception) {
        $resultado = -99;
        echo json_encode($resultado);
    }
}

function cerrarSesion()
{
    $_SESSION = [];

    session_destroy();
    exit;
}

function anadirCarta()
{
    $idUsuario = $_SESSION['usuario']['id'];

    try {
        $resultado = PA_AñadirNuevaCartaAColeccion($idUsuario);

        if ($resultado === 0) {
            $_SESSION['usuario']['fechaHoraUltimaObtencionCarta'] = date('Y-m-d H:i:s');
            $_SESSION['coleccion'] = PA_ObtenerColeccion($idUsuario);

            /* CONSTRUIR EL MENSAJE DE VUELTA A JAVASCRIPT */
            $respuesta = [
            'status'          => 'exito',
            'mensaje'         => "Servidor PHP: Carta añadida correctamente",
            'resultado'       => $resultado,
            'usuario'         => $_SESSION['usuario'],
            'coleccion'       => $_SESSION['coleccion']
            ];
            echo json_encode($respuesta);

        }
    } catch (Throwable $exception) {
       $resultado = -99;
        echo json_encode($resultado);
    }
    exit;
}
function entrarTrade()
{
    //CAMBIAR EL ESTADO DEL USUARIO A 'LISTO_NEGOCIAR'
    if($_SESSION['usuario']['listo_negociar'] == 0)
    {
        if(PA_CambiarListoParaNegociar($_SESSION['usuario']['id']) == 0)
        {
            $_SESSION['usuario']['listo_negociar'] == 1;
        }
    }
    //OBTENER LA LISTA DE USUARIOS LISTOS PARA NEGOCIAR
    $lista_usuarios_trade = PA_ObtenerUsuariosParaTrade();
    $_SESSION['lista_usuarios_trade'] = $lista_usuarios_trade;

}

$accion = $_GET['accion'];

switch ($accion) {
    case 'login':
        iniciarSesion();
        break;
    case 'registro':
        registrarUsuario();
        break;
    case 'logout':
        cerrarSesion();
        break;
    case 'anadir-carta':
        anadirCarta();
        break;
    case 'entrar-a-trade':
        entrarTrade();
        break;
    default:
        responder('Acción no encontrada.', 404);
}
