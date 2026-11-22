<?php
/* ============================================================
   menu.php
   ------------------------------------------------------------------
   Cabecera que se repite en todas las paginas privadas.
   Estructura: logo - navegacion - carta diaria y salir.
   ============================================================ */

// Asegurarnos de que la sesión esté iniciada por si no se llamó antes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sabemos en que pagina estamos para marcar el enlace activo
$pagina_actual = basename($_SERVER["PHP_SELF"]);

// ¿Ya ha cogido la carta de hoy? (el dato lo da la capa de datos: dal/UsuarioDAL.php)
$menu_usuario_id = (int) ($_SESSION["usuario_id"] ?? 0);
$menu_ultima     = $menu_usuario_id > 0 ? obtenerUltimaObtencion($menu_usuario_id) : null;

$menu_obtenida_hoy = ($menu_ultima !== null
    && substr($menu_ultima, 0, 10) === date("Y-m-d"));

// Enlaces de navegacion: [archivo, icono, texto]
$menu_enlaces = [
    ["inicio.php",    "bx-home",       "Inicio"],
    ["coleccion.php", "bx-collection", "Mi colección"],
    ["dinopedia.php", "bx-book-open",  "DinoPedia"],
    ["perfil.php",    "bx-user",       "Mi perfil"],
];
?>
<header class="topbar">
    <a class="logo" href="inicio.php">
        <span class="logo-mark" aria-hidden="true"><i class="bx bx-id-card"></i></span>
        <span class="logo-texto">
            <strong>DinoCards</strong>
            <small>Club de coleccionistas</small>
        </span>
    </a>

    <nav class="nav" aria-label="Principal">
        <?php foreach ($menu_enlaces as [$url, $icono, $texto]): ?>
            <a href="<?= escapar($url) ?>" class="nav-link<?= $pagina_actual === $url ? " activo" : "" ?>">
                <i class="bx <?= escapar($icono) ?>"></i><span><?= escapar($texto) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="topbar-fin">
        <a class="carta-badge<?= $menu_obtenida_hoy ? " abierta" : "" ?>" href="inicio.php" title="Carta diaria">
            <i class="bx <?= $menu_obtenida_hoy ? "bx-check-circle" : "bx-envelope-open" ?>"></i>
            <span class="carta-badge-txt">
                <small>Carta diaria</small>
                <b><?= $menu_obtenida_hoy ? "Abierta" : "Disponible" ?></b>
            </span>
            <?php if (!$menu_obtenida_hoy): ?>
                <span class="punto" title="Tienes tu carta diaria disponible"></span>
            <?php endif; ?>
        </a>

        <a class="nav-link salir" href="php/cerrar.php" title="Cerrar sesión">
            <i class="bx bx-log-out"></i>
            <span>Salir</span>
        </a>
    </div>
</header>