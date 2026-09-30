<?php

$host = '127.0.0.1';
$port = 3306;
$database = 'sakila';
$username = 'root';
$password = '@lb0r@d@2026';

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);

$error = '';
$films = [];
$totalFilms = 0;
$averageRating = 0;
$averageLength = 0;
$totalAlquileres = 0;


if ($error === ''){
    $alquileresResult = $connection->query("call num_total_alquileres");
    if ($alquileresResult){
        $alquileres = $alquileresResult->fetch_assoc();
        $totalAlquileres = (int) $alquileres["total_alquileres"];
        $alquileresResult->free();
    } else {

        $error = "No se pudo ejecutar el procedimiento total_alquieler: "
        . $connection->error;
    }
}

// vaciar resultadoso pendientes tras el call
while ($connection->more_results()){
    $connection->next_result();
}

$connection->close();


/*
if ($connection->connect_error) {
	$error = 'No se pudo conectar con MySQL: ' . $connection->connect_error;
} else {
	$connection->set_charset('utf8mb4');

    $result = $connection->query('CALL num_total_alquileres()');

    if ($result) {
        $row = $result->fetch_assoc();
        $totalAlquileres = $row['total_alquileres'];
        $result->free();
    }

    while ($connection->more_results()){
        $connection->next_result();
    }

	$connection->close();
}

function escapeHtml($value): string
{
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
*/

?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Total de alquileres | Sakila</title>
	<link rel="stylesheet" href="style/estilos.css">
</head>
<body>
	<main class="dashboard">
		<header>
			<div>
				<h1>Total de alquileres</h1>
				<p class="subtitle">Cantidad devuelta por el procedimiento almacenado <strong>TotalAlquileres</strong></p>
			</div>
			<div class="connection">MySQL · localhost:3306 · sakila</div>
		</header>

		<?php if ($error !== ''): ?>
			<div class="error" role="alert"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
		<?php else: ?>
			<section class="metrics" aria-label="Total de alquileres">
				<article class="metric">
					<span class="metric-label">Alquileres</span>
					<span class="metric-value"><?= number_format($totalAlquileres) ?></span>
				</article>
			</section>
		<?php endif; ?>
	</main>
</body>
</html>