<?php

require_once __DIR__ . '/../src/AccesoDatos.php';

if (!esta_logueado()) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../inicio.php');
    exit;
}

$_SESSION['sobre_flash'] = abrirSobreDelDia((int) $_SESSION['usuario_id']);

header('Location: ../inicio.php');
exit;
