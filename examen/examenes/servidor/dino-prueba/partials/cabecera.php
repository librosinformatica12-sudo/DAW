<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../src/AccesoDatos.php';

if (!function_exists('ce')) {
    function ce(mixed $v): string {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

$cabId      = isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null;
$cabDatos   = $_SESSION['usuario_datos'] ?? [];
$cabUsuario = $cabId !== null ? obtenerUsuarioPorId($cabId) : null;

$cabNombre  = $cabUsuario['username']         ?? $cabDatos['username'] ?? 'Coleccionista';
$cabUltima  = $cabUsuario['ultima_obtencion'] ?? ($cabDatos['ultima_obtencion'] ?? null);

$cabObtenidaHoy = $cabUltima !== null
    && date('Y-m-d', strtotime((string) $cabUltima)) === date('Y-m-d');

$cabActual = basename($_SERVER['PHP_SELF'] ?? '');

$cabNav = [
    ['inicio.php',    'bx-home',       'Inicio'],
    ['coleccion.php', 'bx-collection', 'Mi colección'],
    ['perfil.php',    'bx-user',       'Mi Perfil'],
];
?>
<header class="topbar">
    <a class="logo logo-app" href="inicio.php">
        <span class="logo-mark" aria-hidden="true"><i class="bx bx-id-card"></i></span>
        <span class="logo-texto">
            <strong>DinoCards</strong>
            <small>Club de coleccionistas</small>
        </span>
    </a>

    <nav class="nav" aria-label="Principal">
        <?php foreach ($cabNav as [$url, $icono, $texto]): ?>
            <?php if (file_exists(__DIR__ . '/../' . $url)): ?>
                <a href="<?= ce($url) ?>" class="nav-link<?= $cabActual === $url ? ' activo' : '' ?>">
                    <i class="bx <?= ce($icono) ?>"></i><span><?= ce($texto) ?></span>
                </a>
            <?php else: ?>
                <span class="nav-link prox" title="Próximamente">
                    <i class="bx <?= ce($icono) ?>"></i><span><?= ce($texto) ?></span><small>pronto</small>
                </span>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <div class="topbar-fin">
        <a class="carta-badge<?= $cabObtenidaHoy ? ' abierta' : '' ?>" href="inicio.php" title="Carta diaria">
            <i class="bx <?= $cabObtenidaHoy ? 'bx-check-circle' : 'bx-envelope-open' ?>"></i>
            <span class="carta-badge-txt">
                <small>Carta diaria</small>
                <b><?= $cabObtenidaHoy ? 'Abierta' : 'Disponible' ?></b>
            </span>
            <?php if (!$cabObtenidaHoy): ?>
                <span class="punto" title="Tienes tu carta diaria disponible"></span>
            <?php endif; ?>
        </a>

        <a class="nav-link salir" href="php/logout.php" title="Cerrar sesión">
            <i class="bx bx-log-out"></i>
            <span>Salir</span>
        </a>
    </div>
</header>