<?php
/**
 * cabecera.php
 * ------------------------------------------------------------
 * Parte superior común (head + cabecera de navegación).
 * Cada página define antes la variable $titulo.
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/funciones.php';

$titulo = $titulo ?? 'DinoCards';
$pagina = basename($_SERVER['PHP_SELF']);

// Enlaces del menú (solo se muestran si hay sesión)
$menu = [
    'inicio.php'    => 'Inicio',
    'coleccion.php' => 'Mi colección',
    'dinopedia.php' => 'DinoPedia',
    'perfil.php'    => 'Mi perfil',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escapar($titulo) ?> · DinoCards</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <header class="cabecera">
        <a class="marca" href="<?= esta_logueado() ? 'inicio.php' : 'index.php' ?>">
            Dino<span>Cards</span>
        </a>

        <?php if (esta_logueado()): ?>
            <nav class="nav">
                <?php foreach ($menu as $url => $texto): ?>
                    <a href="<?= $url ?>" class="<?= $pagina === $url ? 'activo' : '' ?>"><?= $texto ?></a>
                <?php endforeach; ?>
                <a href="logout.php" class="salir">Salir</a>
            </nav>
        <?php endif; ?>
    </header>

    <main class="contenedor">
