<?php
session_start();

if (!isset($_SESSION['staff_id'])) {
    header('Location: index.php');
    exit;
}

function h($s) { 
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel | Catálogo Sakila</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
		<h1>Bienvenido, <?= h($_SESSION['login']) ?></h1>
		<p>ID de empleado: <?= (int) $_SESSION['staff_id'] ?></p>
		<p><a href="logout.php">Cerrar sesión</a></p>
	</div>
</body>
</html>