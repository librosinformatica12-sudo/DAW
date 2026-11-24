<?php

session_start();
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/AccesoDatos.php';

function responder(string $mensaje, int $status): void
{
    http_response_code($status);
    $mensajeSeguro = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="es"><meta charset="UTF-8"><title>DinoCards</title>';
    echo '<body><p>' . $mensajeSeguro . '</p><a href="index.php">Volver</a></body></html>';
    exit;
}

function iniciarSesion(): void
{
    $email = $_POST['email'] ?? '';
    $contrasena = $_POST['password'] ?? '';

    if (!is_string($email) || !is_string($contrasena)) {
        responder('Los datos enviados no son válidos.', 400);
    }

    try {
        $login = PA_Login($email, $contrasena);
        if ($login['resultado'] !== 0 || !is_array($login['usuario'])) {
            responder('Correo o contraseña incorrectos.', 401);
        }

        $usuario = $login['usuario'];
        $idUsuario = filter_var($usuario['id'] ?? null, FILTER_VALIDATE_INT);
        if ($idUsuario === false || $idUsuario < 1) {
            error_log('El procedimiento de login no devolvió un id de usuario válido.');
            responder('No se pudo iniciar sesión. Inténtalo de nuevo.', 500);
        }

        $coleccion = PA_ObtenerColeccion($idUsuario);

        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id' => $idUsuario,
            'username' => $usuario['username'] ?? $usuario['nick'] ?? null,
            'email' => $usuario['email'] ?? trim($email),
            'fechaHoraUltimaObtencionCarta' => $usuario['ultima_obtencion'] ?? null,
            'listos_negociar' => $usuario["listos_negociar"] ?? null,
        ];
        $_SESSION['coleccion'] = $coleccion['coleccion'];

        header('Location: index.php');
        exit;
    } catch (InvalidArgumentException $exception) {
        responder($exception->getMessage(), 422);
    } catch (Throwable $exception) {
        error_log($exception->__toString());
        responder('No se pudo iniciar sesión. Inténtalo de nuevo.', 500);
    }
}

function registrarUsuario(): void
{
    $nick = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $contrasena = $_POST['password'] ?? '';
    $confirmacion = $_POST['confirmPassword'] ?? '';

    if (!is_string($nick) || !is_string($email) || !is_string($contrasena) || !is_string($confirmacion)) {
        responder('Los datos enviados no son válidos.', 400);
    }

    if ($contrasena !== $confirmacion) {
        responder('Las contraseñas no coinciden.', 422);
    }

    try {
        $resultado = PA_Registrar($nick, $email, $contrasena);
        responder('Respuesta del sistema de registro: ' . (string) $resultado, 200);
    } catch (InvalidArgumentException $exception) {
        responder($exception->getMessage(), 422);
    } catch (Throwable $exception) {
        error_log($exception->__toString());
        responder('No se pudo completar el registro. Inténtalo de nuevo.', 500);
    }
}

function cerrarSesion(): void
{
    $_SESSION = [];

    session_destroy();
    header('Location: index.php');
    exit;
}

function anadirCarta(): void
{
    $idUsuario = $_SESSION['usuario']['id'];

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
        $_SESSION['mensajeColeccion'] = 'Podrás obtener otra carta cuando hayan pasado 24 horas desde la última.';
        header('Location: index.php');
        exit;
    }

    try {
        $resultado = PA_AñadirNuevaCartaAColeccion($idUsuario);

        if ($resultado === 0) {
            $_SESSION['usuario']['fechaHoraUltimaObtencionCarta'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
            $coleccion = PA_ObtenerColeccion($idUsuario);
            if ($coleccion['resultado'] !== 0) {
                responder('No se pudo actualizar la colección. Inténtalo de nuevo.', 500);
            }

            $_SESSION['coleccion'] = $coleccion['coleccion'];
            $_SESSION['mensajeColeccion'] = 'Carta añadida a tu colección.';
        } else {
            $_SESSION['mensajeColeccion'] = 'No se pudo añadir la carta. Código: ' . $resultado;
        }
    } catch (Throwable $exception) {
        error_log($exception->__toString());
        $_SESSION['mensajeColeccion'] = 'No se pudo añadir la carta. Inténtalo de nuevo.';
    }

    header('Location: index.php');
    exit;
}

function entrarTrade(): void
{
    // CAMBIAR EL ESTADO DEL USUARIO A 'LISTO_NEGOCIAR'
    if($_SESSION["usuario"]["listos_negociar"] == 0)
    {
        PA_CambiarListoParaNegociar($_SESSION["usuario"]["id"]);
    }
    // OBTENER LA LISTA DE USUARIOS LISTOS PARA NEGOCIAR

        $lista_usuarios_trade = PA_ObtenerUsuariosParaTrade();
        $_SESSION["lista_usuarios_trade"] = $lista_usuarios_trade;

    // NAVEGAR AL AREA DE TRADE Y RELLENAR LA LISTA DE USUARIOS VISUAL
        header('Location: index.php');
}


// permite leer los action q estan en index
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
    default:
        responder('Acción no encontrada.', 404);
}
