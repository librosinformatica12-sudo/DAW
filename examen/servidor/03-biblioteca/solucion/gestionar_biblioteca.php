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
$tipoMensaje = 'ok';

$accion = $_POST['accion'] ?? '';

// =====================================================
// 3. ACCIONES
// =====================================================

// ---------- ALTA DE LIBRO ----------
if ($accion === 'alta_libro') {
    $titulo     = trim($_POST['titulo'] ?? '');
    $autor      = trim($_POST['autor'] ?? '');
    $ejemplares = (int)($_POST['ejemplares'] ?? 0);

    $res = PA_AltaLibro($titulo, $autor, $ejemplares);

    if ($res === -1) {
        $mensaje = 'El título no puede estar vacío.';
        $tipoMensaje = 'error';
    } elseif ($res === -2) {
        $mensaje = 'El autor no puede estar vacío.';
        $tipoMensaje = 'error';
    } elseif ($res === -3) {
        $mensaje = 'Los ejemplares deben ser mayores que 0.';
        $tipoMensaje = 'error';
    } else {
        $mensaje = 'Libro dado de alta.';
    }
}

// ---------- ALTA DE SOCIO ----------
if ($accion === 'alta_socio') {
    $nombre = trim($_POST['nombre'] ?? '');

    $res = PA_AltaSocio($nombre);

    if ($res === 0) {
        $mensaje = 'Socio dado de alta.';
    } else {
        $mensaje = 'El nombre del socio no puede estar vacío.';
        $tipoMensaje = 'error';
    }
}

// ---------- PRESTAR ----------
if ($accion === 'prestar') {
    $libro = (int)($_POST['id_libro'] ?? 0);
    $socio = (int)($_POST['id_socio'] ?? 0);

    $res = PA_PrestarLibro($libro, $socio);

    if ($res === 0) {
        $mensaje = 'Préstamo realizado.';
    } elseif ($res === -1) {
        $mensaje = 'El libro no existe.';
        $tipoMensaje = 'error';
    } elseif ($res === -2) {
        $mensaje = 'El socio no existe.';
        $tipoMensaje = 'error';
    } elseif ($res === -3) {
        $mensaje = 'No quedan ejemplares disponibles de ese libro.';
        $tipoMensaje = 'error';
    } else {
        $mensaje = 'No se pudo realizar el préstamo.';
        $tipoMensaje = 'error';
    }
}

// ---------- DEVOLVER ----------
if ($accion === 'devolver') {
    $idPrestamo = (int)($_POST['id_prestamo'] ?? 0);

    $res = PA_DevolverLibro($idPrestamo);

    if ($res === 0) {
        $mensaje = 'Libro devuelto correctamente.';
    } elseif ($res === -2) {
        $mensaje = 'Ese préstamo ya estaba devuelto.';
        $tipoMensaje = 'error';
    } else {
        $mensaje = 'El préstamo no existe.';
        $tipoMensaje = 'error';
    }
}

// =====================================================
// 4. DATOS PARA PINTAR
// =====================================================
$libros = PA_ListaLibros();
$prestamos = PA_ListaPrestamos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Biblioteca</title>
</head>
<body>

    <h1>Biblioteca</h1>

    <?php if ($mensaje !== ''): ?>
        <p style="font-weight:bold; color: <?= $tipoMensaje === 'ok' ? 'green' : 'red' ?>;">
            <?= htmlspecialchars($mensaje) ?>
        </p>
    <?php endif; ?>

    <!-- ================= LIBROS ================= -->
    <h2>Libros (<?= count($libros) ?>)</h2>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Autor</th>
                <th>Ejemplares</th>
                <th>Disponibles</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($libros as $libro): ?>
                <tr>
                    <td><?= htmlspecialchars($libro['id_libro']) ?></td>
                    <td><?= htmlspecialchars($libro['titulo']) ?></td>
                    <td><?= htmlspecialchars($libro['autor']) ?></td>
                    <td><?= htmlspecialchars($libro['ejemplares']) ?></td>
                    <td><?= htmlspecialchars($libro['disponibles']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <hr>

    <!-- ================= PRÉSTAMOS ================= -->
    <h2>Préstamos activos (<?= count($prestamos) ?>)</h2>

    <?php if (count($prestamos) > 0): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Libro</th>
                    <th>Socio</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prestamos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['id_prestamo']) ?></td>
                        <td><?= htmlspecialchars($p['titulo']) ?></td>
                        <td><?= htmlspecialchars($p['socio']) ?></td>
                        <td><?= htmlspecialchars($p['fecha_prestamo']) ?></td>
                        <td>
                            <form action="gestionar_biblioteca.php" method="post">
                                <input type="hidden" name="accion" value="devolver">
                                <input type="hidden" name="id_prestamo" value="<?= htmlspecialchars($p['id_prestamo']) ?>">
                                <button type="submit">Devolver</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay préstamos activos.</p>
    <?php endif; ?>

    <hr>

    <!-- ================= FORMULARIOS ================= -->
    <h2>Alta de libro</h2>
    <form action="gestionar_biblioteca.php" method="post">
        <input type="hidden" name="accion" value="alta_libro">

        <label>Título: <input type="text" name="titulo" maxlength="80" required></label><br><br>
        <label>Autor:  <input type="text" name="autor" maxlength="60" required></label><br><br>
        <label>Ejemplares: <input type="number" name="ejemplares" min="1" required></label><br><br>

        <button type="submit">Guardar libro</button>
    </form>

    <h2>Alta de socio</h2>
    <form action="gestionar_biblioteca.php" method="post">
        <input type="hidden" name="accion" value="alta_socio">

        <label>Nombre: <input type="text" name="nombre" maxlength="60" required></label><br><br>

        <button type="submit">Guardar socio</button>
    </form>

    <h2>Prestar libro</h2>
    <form action="gestionar_biblioteca.php" method="post">
        <input type="hidden" name="accion" value="prestar">

        <label>ID del libro: <input type="number" name="id_libro" required></label><br><br>
        <label>ID del socio: <input type="number" name="id_socio" required></label><br><br>

        <button type="submit">Prestar</button>
    </form>

    <br>
    <a href="portal_biblioteca.html">Volver al portal</a>

</body>
</html>
