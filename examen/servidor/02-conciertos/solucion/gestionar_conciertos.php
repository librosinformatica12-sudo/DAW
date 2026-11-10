<?php
// =====================================================
// 1. DEPENDENCIAS Y SESIÓN
// =====================================================
require_once __DIR__ . '/AccesoDatos.php';
session_start();

// =====================================================
// 2. VARIABLES
// =====================================================
$mensaje = '';
$tipoMensaje = 'ok';   // ok | error

$accion = $_POST['accion'] ?? '';

function fechaMysql(string $entrada): string
{
    if ($entrada === '') {
        return '';
    }
    return date('Y-m-d H:i:s', strtotime($entrada));
}

// =====================================================
// 3. ACCIONES
// =====================================================

// ---------- ALTA ----------
if ($accion === 'alta') {
    $nombre       = trim($_POST['nombre_banda'] ?? '');
    $fechaEntrada = trim($_POST['fecha_concierto'] ?? '');
    $precio       = (float)($_POST['precio'] ?? 0);
    $aforo        = (int)($_POST['aforo'] ?? 0);

    $fecha = fechaMysql($fechaEntrada);

    if ($nombre === '') {
        $mensaje = 'El nombre de la banda no puede estar vacío.';
        $tipoMensaje = 'error';
    } elseif ($fechaEntrada === '') {
        $mensaje = 'La fecha no puede estar vacía.';
        $tipoMensaje = 'error';
    } else {
        $res = PA_AltaConcierto($nombre, $fecha, $precio, $aforo);

        if ($res === -1) {
            $mensaje = 'Nombre vacío.';
            $tipoMensaje = 'error';
        } elseif ($res === -2) {
            $mensaje = 'Fecha vacía.';
            $tipoMensaje = 'error';
        } elseif ($res === -3) {
            $mensaje = 'La fecha debe ser futura.';
            $tipoMensaje = 'error';
        } elseif ($res === -4) {
            $mensaje = 'El precio debe ser mayor que 0.';
            $tipoMensaje = 'error';
        } elseif ($res === -5) {
            $mensaje = 'El aforo debe ser mayor que 0.';
            $tipoMensaje = 'error';
        } else {
            $mensaje = 'Concierto guardado correctamente.';
        }
    }
}

// ---------- MODIFICAR ----------
if ($accion === 'modificar') {
    $id           = (int)($_POST['id_concierto'] ?? 0);
    $nombre       = trim($_POST['nombre_banda'] ?? '');
    $fechaEntrada = trim($_POST['fecha_concierto'] ?? '');
    $precio       = (float)($_POST['precio'] ?? 0);
    $aforo        = (int)($_POST['aforo'] ?? 0);

    $fecha = fechaMysql($fechaEntrada);

    if ($id <= 0 || $nombre === '' || $fechaEntrada === '') {
        $mensaje = 'Rellena ID, nombre y fecha para modificar.';
        $tipoMensaje = 'error';
    } else {
        $res = PA_ModificarConcierto($id, $nombre, $fecha, $precio, $aforo);

        if ($res === 0) {
            $mensaje = 'Concierto modificado.';
        } elseif ($res === -6) {
            $mensaje = 'Ese concierto no existe.';
            $tipoMensaje = 'error';
        } elseif ($res === -1) {
            $mensaje = 'Nombre vacío.';
            $tipoMensaje = 'error';
        } elseif ($res === -2) {
            $mensaje = 'Fecha vacía.';
            $tipoMensaje = 'error';
        } elseif ($res === -3) {
            $mensaje = 'La fecha debe ser futura.';
            $tipoMensaje = 'error';
        } elseif ($res === -4) {
            $mensaje = 'El precio debe ser mayor que 0.';
            $tipoMensaje = 'error';
        } elseif ($res === -5) {
            $mensaje = 'El aforo debe ser mayor que 0.';
            $tipoMensaje = 'error';
        } else {
            $mensaje = 'No se pudo modificar (código ' . $res . ').';
            $tipoMensaje = 'error';
        }
    }
}

// ---------- BORRAR ----------
if ($accion === 'borrar') {
    $id = (int)($_POST['id_concierto'] ?? 0);
    $res = PA_BorrarConcierto($id);

    if ($res === 0) {
        $mensaje = 'Concierto borrado.';
    } elseif ($res === -2) {
        $mensaje = 'No se puede borrar: ya hay entradas vendidas.';
        $tipoMensaje = 'error';
    } else {
        $mensaje = 'No se pudo borrar: el concierto no existe.';
        $tipoMensaje = 'error';
    }
}

// ---------- COMPRAR ENTRADA ----------
if ($accion === 'comprar') {
    $id = (int)($_POST['id_concierto'] ?? 0);
    $res = PA_ComprarEntrada($id);

    if ($res === 0) {
        $mensaje = 'Entrada comprada correctamente.';
    } elseif ($res === -2) {
        $mensaje = 'No se pueden comprar entradas para un concierto pasado.';
        $tipoMensaje = 'error';
    } elseif ($res === -3) {
        $mensaje = 'No quedan entradas: aforo completo.';
        $tipoMensaje = 'error';
    } else {
        $mensaje = 'El concierto no existe.';
        $tipoMensaje = 'error';
    }
}

// =====================================================
// 4. DATOS PARA PINTAR
// =====================================================
$conciertos = PA_ListaConciertos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestor de conciertos</title>
</head>
<body>

    <h1>Gestor de conciertos</h1>

    <?php if ($mensaje !== ''): ?>
        <p style="font-weight:bold; color: <?= $tipoMensaje === 'ok' ? 'green' : 'red' ?>;">
            <?= htmlspecialchars($mensaje) ?>
        </p>
    <?php endif; ?>

    <h2>Conciertos (<?= count($conciertos) ?>)</h2>

    <?php if (count($conciertos) > 0): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Banda</th>
                    <th>Fecha</th>
                    <th>Precio</th>
                    <th>Aforo</th>
                    <th>Vendidas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($conciertos as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['id_concierto']) ?></td>
                        <td><?= htmlspecialchars($c['nombre_banda']) ?></td>
                        <td><?= htmlspecialchars($c['fecha_concierto']) ?></td>
                        <td><?= htmlspecialchars($c['precio']) ?> €</td>
                        <td><?= htmlspecialchars($c['aforo']) ?></td>
                        <td><?= htmlspecialchars($c['entradas_vendidas']) ?></td>
                        <td>
                            <form action="gestionar_conciertos.php" method="post" style="display:inline;">
                                <input type="hidden" name="accion" value="comprar">
                                <input type="hidden" name="id_concierto" value="<?= htmlspecialchars($c['id_concierto']) ?>">
                                <button type="submit">Comprar entrada</button>
                            </form>

                            <form action="gestionar_conciertos.php" method="post" style="display:inline;">
                                <input type="hidden" name="accion" value="borrar">
                                <input type="hidden" name="id_concierto" value="<?= htmlspecialchars($c['id_concierto']) ?>">
                                <button type="submit">Borrar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay conciertos todavía.</p>
    <?php endif; ?>

    <hr>

    <!-- ================= ALTA ================= -->
    <h2>Dar de alta un concierto</h2>
    <form action="gestionar_conciertos.php" method="post">
        <input type="hidden" name="accion" value="alta">

        <label>Nombre de la banda:
            <input type="text" name="nombre_banda" maxlength="60" required>
        </label><br><br>

        <label>Fecha y hora:
            <input type="datetime-local" name="fecha_concierto" required>
        </label><br><br>

        <label>Precio (€):
            <input type="number" name="precio" min="0.01" step="0.01" required>
        </label><br><br>

        <label>Aforo:
            <input type="number" name="aforo" min="1" required>
        </label><br><br>

        <button type="submit">Guardar concierto</button>
    </form>

    <hr>

    <!-- ============== MODIFICAR =============== -->
    <h2>Modificar concierto</h2>
    <form action="gestionar_conciertos.php" method="post">
        <input type="hidden" name="accion" value="modificar">

        <label>ID del concierto:
            <input type="number" name="id_concierto" required>
        </label><br><br>

        <label>Nombre de la banda:
            <input type="text" name="nombre_banda" maxlength="60" required>
        </label><br><br>

        <label>Fecha y hora:
            <input type="datetime-local" name="fecha_concierto" required>
        </label><br><br>

        <label>Precio (€):
            <input type="number" name="precio" min="0.01" step="0.01" required>
        </label><br><br>

        <label>Aforo:
            <input type="number" name="aforo" min="1" required>
        </label><br><br>

        <button type="submit">Modificar concierto</button>
    </form>

    <br>
    <a href="portal_conciertos.html">Volver al portal</a>

</body>
</html>
