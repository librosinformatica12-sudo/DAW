<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/src/AccesoDatos.php';

function e(mixed $v): string {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

$datos   = $_SESSION['usuario_datos'] ?? [];
$usuarioId = $_SESSION['usuario_id'] ?? null;

$nombre      = $datos['username']    ?? $usuarioId;
$email       = $datos['email']       ?? '';
$registro    = $datos['fecha_registro'] ?? null;
$ultimaCarta = $datos['ultima_obtencion'] ?? null;

$totalCartas = null;
if (!$connection->connect_errno && is_numeric($usuarioId)) {
    $stmt = $connection->prepare('SELECT COUNT(*) AS total FROM colecciones WHERE usuario_id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $usuarioId);
        if ($stmt->execute()) {
            $fila = $stmt->get_result()->fetch_assoc();
            $totalCartas = (int) ($fila['total'] ?? 0);
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DinoCards - Mi panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header class="topbar">
        <div class="logo">
            <i class="bx bx-id-card"></i>
            <div class="logo-text">
                <strong>DinoCards<span class="version-code"> V1.0</span></strong>
                <span>CLUB DE COLECCIONISTAS</span>
            </div>
        </div>
        <div class="topbar-der">
            <span><?= e($nombre) ?></span>
            <i class="bx bx-user-circle"></i>
        </div>
    </header>

    <main class="pagina">
        <section class="hero">
            <h2>Hola de nuevo, <span><?= e($nombre) ?></span></h2>
            <p>Ya estás dentro de tu panel. Desde aquí puedes ver el estado de tu colección y gestionar tu cuenta de DinoCards.</p>
            <ul>
                <li><i class="bx bx-gift"></i><div><b>Carta diaria</b><small>Abre tu sobre y consigue un dinosaurio nuevo.</small></div></li>
                <li><i class="bx bx-collection"></i><div><b>Mi colección</b><small>Consulta las cartas que ya tienes.</small></div></li>
                <li><i class="bx bx-book-open"></i><div><b>DinoPedia</b><small>Explora el catálogo completo de especies.</small></div></li>
            </ul>
        </section>

        <div class="container panel-inicio">
            <h2>Mi cuenta</h2>
            <br>
            <p class="sub">Datos de tu sesión actual en DinoCards</p>
            <br>
            
            <ul class="datos-cuenta">
                <li>
                    <i class="bx bx-user"></i>
                    <div><small>Usuario</small><b><?= e($nombre) ?></b></div>
                </li>
                <li>
                    <i class="bx bx-envelope"></i>
                    <div><small>Correo electrónico</small><b><?= e($email !== '' ? $email : '—') ?></b></div>
                </li>
                <li>
                    <i class="bx bx-calendar"></i>
                    <div><small>Miembro desde</small><b><?= e($registro !== null ? date('d/m/Y', strtotime($registro)) : '—') ?></b></div>
                </li>
                <li>
                    <i class="bx bx-time"></i>
                    <div><small>Última carta obtenida</small><b><?= e($ultimaCarta !== null ? date('d/m/Y H:i', strtotime($ultimaCarta)) : 'Todavía ninguna') ?></b></div>
                </li>
            </ul>

            <div class="stat-carta">
                <i class="bx bx-cube"></i>
                <div>
                    <strong><?= $totalCartas !== null ? e($totalCartas) : '—' ?></strong>
                    <small>cartas en tu colección</small>
                </div>
            </div>

            <div class="acciones-inicio">
                <a class="enviar" href="index.php"><i class="bx bx-collection"></i> Ver mi colección</a>
                <a class="salir" href="php/logout.php"><i class="bx bx-log-out"></i> Cerrar sesión</a>
            </div>

            <p class="nota" style="text-align: center;">Portal exclusivo para miembros y coleccionistas de DinoCards</p>
        </div>
    </main>
</body>
</html>
