<?php
/* ============================================================
   menu.php
   ------------------------------------------------------------------
   Barra de navegacion que se repite en todas las paginas privadas.
   ============================================================ */

// Sabemos en que pagina estamos para marcar el enlace activo
$pagina_actual = basename($_SERVER["PHP_SELF"]);
?>
<header class="topbar">
    <span class="titulo">DinoCards</span>
    <nav>
        <a href="inicio.php" class="<?= $pagina_actual === "inicio.php" ? "activo" : "" ?>">Inicio</a>
        <a href="coleccion.php" class="<?= $pagina_actual === "coleccion.php" ? "activo" : "" ?>">Mi colección</a>
        <a href="dinopedia.php" class="<?= $pagina_actual === "dinopedia.php" ? "activo" : "" ?>">DinoPedia</a>
        <a href="perfil.php" class="<?= $pagina_actual === "perfil.php" ? "activo" : "" ?>">Mi perfil</a>
        <a href="cerrar.php">Salir</a>
    </nav>
</header>
