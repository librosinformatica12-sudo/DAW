<?php

session_start();

require_once __DIR__ . '/AccesoDatos.php';

function responder(array $respuesta, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

function responderError(string $mensaje, int $status, int $codigo = -99): void
{
    responder([
        'status' => 'error',
        'mensaje' => $mensaje,
        'resultado' => $codigo,
    ], $status);
}

function iniciarSesion(): void
{
    $email = $_POST['email'] ?? '';
    $contrasena = $_POST['password'] ?? '';

    if (!is_string($email) || !is_string($contrasena)) {
        responderError('Los datos enviados no son válidos.', 400);
    }

    try {
        $login = PA_Login($email, $contrasena);
        if ($login['resultado'] !== 0 || !is_array($login['usuario'])) {
            responderError('Correo o contraseña incorrectos.', 401, (int) $login['resultado']);
        }

        $usuario = $login['usuario'];
        $idUsuario = filter_var($usuario['id'] ?? null, FILTER_VALIDATE_INT);
        if ($idUsuario === false || $idUsuario < 1) {
            error_log('El procedimiento de login no devolvió un id de usuario válido.');
            responderError('No se pudo iniciar sesión. Inténtalo de nuevo.', 500);
        }

        $coleccion = PA_ObtenerColeccion($idUsuario);
        if ($coleccion['resultado'] !== 0) {
            responderError('No se pudo cargar la colección. Inténtalo de nuevo.', 500);
        }

        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id' => $idUsuario,
            'username' => $usuario['username'] ?? $usuario['nick'] ?? null,
            'email' => $usuario['email'] ?? trim($email),
            'fechaHoraUltimaObtencionCarta' => $usuario['ultima_obtencion'] ?? null,
            'listo_negociar' => (int) ($usuario['listo_negociar'] ?? $usuario['listos_negociar'] ?? 0),
        ];
        $_SESSION['coleccion'] = $coleccion['coleccion'];

        responder([
            'status' => 'exito',
            'mensaje' => 'Servidor PHP: Login correcto.',
            'resultado' => 0,
            'usuario' => $_SESSION['usuario'],
            'coleccion' => $_SESSION['coleccion'],
        ]);
    } catch (InvalidArgumentException $exception) {
        responderError($exception->getMessage(), 422);
    } catch (Throwable $exception) {
        error_log($exception->__toString());
        responderError('No se pudo iniciar sesión. Inténtalo de nuevo.', 500);
    }
}

function registrarUsuario(): void
{
    $nick = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $contrasena = $_POST['password'] ?? '';
    $confirmacion = $_POST['confirmPassword'] ?? '';

    if (!is_string($nick) || !is_string($email) || !is_string($contrasena) || !is_string($confirmacion)) {
        responderError('Los datos enviados no son válidos.', 400);
    }

    if ($contrasena !== $confirmacion) {
        responderError('Las contraseñas no coinciden.', 422, -3);
    }

    try {
        $resultado = PA_Registrar($nick, $email, $contrasena);
        responder([
            'status' => $resultado === 0 ? 'exito' : 'error',
            'mensaje' => 'Respuesta del sistema de registro: ' . (string) $resultado,
            'resultado' => $resultado,
        ], $resultado === 0 ? 200 : 409);
    } catch (InvalidArgumentException $exception) {
        responderError($exception->getMessage(), 422);
    } catch (Throwable $exception) {
        error_log($exception->__toString());
        responderError('No se pudo completar el registro. Inténtalo de nuevo.', 500);
    }
}

function cerrarSesion(): void
{
    $_SESSION = [];
    session_destroy();
    responder([
        'status' => 'exito',
        'mensaje' => 'Sesión cerrada.',
        'resultado' => 0,
    ]);
}

function anadirCarta(): void
{
    if (!isset($_SESSION['usuario']['id'])) {
        responderError('Debes iniciar sesión para obtener cartas.', 401);
    }

    $idUsuario = (int) $_SESSION['usuario']['id'];
    $ultimaObtencion = $_SESSION['usuario']['fechaHoraUltimaObtencionCarta'] ?? null;
    $puedeObtenerCarta = $ultimaObtencion === null;

    if (is_string($ultimaObtencion)) {
        if (trim($ultimaObtencion) === '' || str_starts_with($ultimaObtencion, '0000-00-00')) {
            $puedeObtenerCarta = true;
        } else {
            try {
                $proximaObtencion = (new DateTimeImmutable($ultimaObtencion))->modify('+24 hours');
                $puedeObtenerCarta = $proximaObtencion->getTimestamp() <= time();
            } catch (Throwable $exception) {
                error_log($exception->__toString());
            }
        }
    }

    if (!$puedeObtenerCarta) {
        responderError('Podrás obtener otra carta cuando hayan pasado 24 horas desde la última.', 409);
    }

    try {
        $resultado = PA_AñadirNuevaCartaAColeccion($idUsuario);
        if ($resultado !== 0) {
            responderError('No se pudo añadir la carta. Código: ' . $resultado, 409, $resultado);
        }

        $_SESSION['usuario']['fechaHoraUltimaObtencionCarta'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $coleccion = PA_ObtenerColeccion($idUsuario);
        if ($coleccion['resultado'] !== 0) {
            responderError('No se pudo actualizar la colección. Inténtalo de nuevo.', 500);
        }

        $_SESSION['coleccion'] = $coleccion['coleccion'];

        responder([
            'status' => 'exito',
            'mensaje' => 'Carta añadida a tu colección.',
            'resultado' => 0,
            'usuario' => $_SESSION['usuario'],
            'coleccion' => $_SESSION['coleccion'],
        ]);
    } catch (Throwable $exception) {
        error_log($exception->__toString());
        responderError('No se pudo añadir la carta. Inténtalo de nuevo.', 500);
    }
}

function entrarTrade(): void
{
    if (!isset($_SESSION['usuario']['id'])) {
        responderError('Debes iniciar sesión para intercambiar cartas.', 401);
    }

    try {
        if ((int) ($_SESSION['usuario']['listo_negociar'] ?? 0) === 0) {
            $cambio = PA_CambiarListoParaNegociar((int) $_SESSION['usuario']['id']);
            if ($cambio === 0) {
                $_SESSION['usuario']['listo_negociar'] = 1;
            }
        }

        $lista = PA_ObtenerUsuariosParaTrade();
        $_SESSION['lista_usuarios_trade'] = $lista['coleccion'];

        responder([
            'status' => 'exito',
            'mensaje' => 'Servidor PHP: lista de exploradores lista.',
            'resultado' => 0,
            'lista_usuarios_trade' => $_SESSION['lista_usuarios_trade'],
        ]);
    } catch (Throwable $exception) {
        error_log($exception->__toString());
        responderError('No se pudo cargar la lista de intercambios.', 500);
    }
}

$accion = $_GET['accion'] ?? '';

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
        responderError('Acción no encontrada.', 404);
}
