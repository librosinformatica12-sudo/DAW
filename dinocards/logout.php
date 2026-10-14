<?php
/**
 * logout.php
 * Cierra la sesión del usuario y vuelve a la página de entrada.
 */
require_once __DIR__ . '/includes/funciones.php';

$_SESSION = [];
session_destroy();

header('Location: index.php');
exit;
