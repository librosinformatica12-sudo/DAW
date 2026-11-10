<?php
// =====================================================
// 1. DEPENDENCIAS Y SESIÓN
// =====================================================
require_once __DIR__ . '/AccesoDatos.php';
session_start();

// =====================================================
// 2. VARIABLES DE TRABAJO
// =====================================================
$mensaje = '';
$tipoMensaje = 'ok';   // ok | error

$especialidades = [
    'Medicina General',
    'Pediatría',
    'Dermatología',
    'Traumatología'
];

$accion = $_POST['accion'] ?? '';

/**
 * Convierte 2026-11-10T18:30 (datetime-local)
 * a       2026-11-10 18:30:00 (MySQL DATETIME)
 */
function fechaMysql(string $entrada): string
{
    if ($entrada === '') {
        return '';
    }
    return date('Y-m-d H:i:s', strtotime($entrada));
}

// =====================================================
// 3. PROCESAR ACCIONES
// =====================================================

// ---------- 3.1 ALTA ----------
if ($accion === 'alta') {
    $paciente     = trim($_POST['paciente'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    $fechaEntrada = trim($_POST['fecha_hora'] ?? '');
    $doctor       = (int)($_POST['doctor'] ?? 0);

    $fecha = fechaMysql($fechaEntrada);

    if ($paciente === '') {
        $mensaje = 'El nombre del paciente no puede estar vacío.';
        $tipoMensaje = 'error';
    } elseif ($especialidad === '' || !in_array($especialidad, $especialidades, true)) {
        $mensaje = 'Debes elegir una especialidad válida.';
        $tipoMensaje = 'error';
    } elseif ($fechaEntrada === '') {
        $mensaje = 'La fecha no puede estar vacía.';
        $tipoMensaje = 'error';
    } elseif (PA_DameDoctor($doctor) === null) {
        $mensaje = 'El doctor ' . $doctor . ' no existe.';
        $tipoMensaje = 'error';
    } else {
        $res = PA_AltaCita($paciente, $especialidad, $fecha, $doctor);

        if ($res === -1) {
            $mensaje = 'Paciente vacío.';
            $tipoMensaje = 'error';
        } elseif ($res === -2) {
            $mensaje = 'Especialidad vacía.';
            $tipoMensaje = 'error';
        } elseif ($res === -3) {
            $mensaje = 'Fecha vacía.';
            $tipoMensaje = 'error';
        } elseif ($res === -4) {
            $mensaje = 'La fecha debe ser posterior al momento actual.';
            $tipoMensaje = 'error';
        } elseif ($res === -5) {
            $mensaje = 'Doctor no válido.';
            $tipoMensaje = 'error';
        } else {
            $mensaje = 'Cita guardada correctamente.';
        }
    }
}

// ---------- 3.2 MODIFICAR ----------
if ($accion === 'modificar') {
    $id           = (int)($_POST['id_cita'] ?? 0);
    $paciente     = trim($_POST['paciente'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    $fechaEntrada = trim($_POST['fecha_hora'] ?? '');
    $doctor       = (int)($_POST['doctor'] ?? 0);

    $fecha = fechaMysql($fechaEntrada);

    if ($id <= 0 || $paciente === '' || $especialidad === '' || $fechaEntrada === '') {
        $mensaje = 'Rellena todos los campos para modificar.';
        $tipoMensaje = 'error';
    } elseif (!in_array($especialidad, $especialidades, true)) {
        $mensaje = 'Especialidad no válida.';
        $tipoMensaje = 'error';
    } elseif (PA_DameDoctor($doctor) === null) {
        $mensaje = 'El doctor ' . $doctor . ' no existe.';
        $tipoMensaje = 'error';
    } else {
        $res = PA_ModificarCita($id, $paciente, $especialidad, $fecha, $doctor);

        if ($res === -5) {
            $mensaje = 'Doctor no válido.';
            $tipoMensaje = 'error';
        } elseif ($res === -4) {
            $mensaje = 'La nueva fecha debe ser posterior a hoy.';
            $tipoMensaje = 'error';
        } elseif ($res === -6) {
            $mensaje = 'Esa cita no existe.';
            $tipoMensaje = 'error';
        } elseif ($res === 0) {
            $mensaje = 'Cita modificada correctamente.';
        } else {
            $mensaje = 'No se pudo modificar la cita (código ' . $res . ').';
            $tipoMensaje = 'error';
        }
    }
}

// ---------- 3.3 BORRAR ----------
if ($accion === 'borrar') {
    $id = (int)($_POST['id_cita'] ?? 0);
    $res = PA_BorrarCita($id);

    if ($res === 0) {
        $mensaje = 'Cita borrada.';
    } else {
        $mensaje = 'No se pudo borrar: la cita no existe.';
        $tipoMensaje = 'error';
    }
}

// =====================================================
// 4. LEER DATOS PARA PINTAR
// =====================================================
$citas = PA_ListaCitas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clínica — Gestión de citas</title>
</head>
<body>

    <h1>Gestión de citas</h1>

    <?php if ($mensaje !== ''): ?>
        <p style="font-weight:bold; color: <?= $tipoMensaje === 'ok' ? 'green' : 'red' ?>;">
            <?= htmlspecialchars($mensaje) ?>
        </p>
    <?php endif; ?>

    <h2>Citas registradas (<?= count($citas) ?>)</h2>

    <?php if (count($citas) > 0): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Paciente</th>
                    <th>Especialidad</th>
                    <th>Fecha</th>
                    <th>Doctor</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($citas as $cita): ?>
                    <tr>
                        <td><?= htmlspecialchars($cita['id_cita']) ?></td>
                        <td><?= htmlspecialchars($cita['paciente']) ?></td>
                        <td><?= htmlspecialchars($cita['especialidad']) ?></td>
                        <td><?= htmlspecialchars($cita['fecha']) ?></td>
                        <td>
                            <?= htmlspecialchars($cita['doctor_nombre'] . ' ' . $cita['doctor_apellidos']) ?>
                            (#<?= htmlspecialchars($cita['fk_doctor']) ?>)
                        </td>
                        <td>
                            <form action="gestionar_citas.php" method="post" style="display:inline;">
                                <input type="hidden" name="accion" value="borrar">
                                <input type="hidden" name="id_cita" value="<?= htmlspecialchars($cita['id_cita']) ?>">
                                <button type="submit">Borrar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay citas todavía.</p>
    <?php endif; ?>

    <hr>

    <!-- ============================================= -->
    <!-- FORMULARIO DE ALTA                            -->
    <!-- ============================================= -->
    <h2>Nueva cita</h2>
    <form action="gestionar_citas.php" method="post">
        <input type="hidden" name="accion" value="alta">

        <label>Paciente:
            <input type="text" name="paciente" maxlength="50" required>
        </label><br><br>

        <label>Especialidad:
            <select name="especialidad" required>
                <option value="">-- Elige --</option>
                <option value="Medicina General">Medicina General</option>
                <option value="Pediatría">Pediatría</option>
                <option value="Dermatología">Dermatología</option>
                <option value="Traumatología">Traumatología</option>
            </select>
        </label><br><br>

        <label>Fecha y hora:
            <input type="datetime-local" name="fecha_hora" required>
        </label><br><br>

        <label>Nº licencia del doctor:
            <input type="number" name="doctor" required>
        </label><br><br>

        <button type="submit">Guardar cita</button>
    </form>

    <hr>

    <!-- ============================================= -->
    <!-- FORMULARIO DE MODIFICACIÓN                    -->
    <!-- ============================================= -->
    <h2>Modificar cita</h2>
    <form action="gestionar_citas.php" method="post">
        <input type="hidden" name="accion" value="modificar">

        <label>ID de la cita:
            <input type="number" name="id_cita" required>
        </label><br><br>

        <label>Paciente:
            <input type="text" name="paciente" maxlength="50" required>
        </label><br><br>

        <label>Especialidad:
            <select name="especialidad" required>
                <option value="Medicina General">Medicina General</option>
                <option value="Pediatría">Pediatría</option>
                <option value="Dermatología">Dermatología</option>
                <option value="Traumatología">Traumatología</option>
            </select>
        </label><br><br>

        <label>Nueva fecha y hora:
            <input type="datetime-local" name="fecha_hora" required>
        </label><br><br>

        <label>Nº licencia del doctor:
            <input type="number" name="doctor" required>
        </label><br><br>

        <button type="submit">Modificar cita</button>
    </form>

    <br>
    <a href="portal_citas.html">Volver al portal</a>

</body>
</html>
