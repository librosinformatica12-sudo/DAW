<?php

$host = '127.0.0.1';
$port = 3306;
$database = 'sakila';
$username = 'oscar';
$password = '561Cazadora#%';

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);

$error = '';
$films = [];
$totalFilms = 0;
$averageRating = 0;
$averageLength = 0;
$totalAlquileres = 0;

if ($connection->connect_error) {
	$error = 'No se pudo conectar con MySQL: ' . $connection->connect_error;
} else {
	$connection->set_charset('utf8mb4');
	$summaryResult = $connection->query(
		"SELECT COUNT(*) AS total_films,
				AVG(rating) AS average_rating,
				AVG(length) AS average_length
		 FROM film"
	);

	$filmsResult = $connection->query(
		"SELECT film_id, title, release_year, rating, length, rental_rate
		 FROM film
		 ORDER BY title"
	);

	// Llamada al procedimiento almacenado TotalAlquieleres()
	if ($connection->query("CALL TotalAlquieleres()")) {
		$procedureResult = $connection->store_result();
		if ($procedureResult) {
			$procedureRow = $procedureResult->fetch_assoc();
			$totalAlquileres = (int) ($procedureRow['total_alquileres'] ?? 0);
			$procedureResult->free();
		}
		// Liberar cualquier resultado adicional que deje el procedimiento
		while ($connection->more_results() && $connection->next_result()) {
			if ($extra = $connection->store_result()) {
				$extra->free();
			}
		}
	}

	if ($summaryResult && $filmsResult) {
		$summary = $summaryResult->fetch_assoc();
		$totalFilms = (int) $summary['total_films'];
		$averageRating = $summary['average_rating'] ?? 0;
		$averageLength = $summary['average_length'] ?? 0;

		while ($film = $filmsResult->fetch_assoc()) {
			$films[] = $film;
		}

		$summaryResult->free();
		$filmsResult->free();
	} else {
		$error = 'No se pudo consultar la tabla film: ' . $connection->error;
	}

	$connection->close();
}

function escapeHtml($value): string
{
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Catálogo Sakila | Film</title>
	<style>
		:root {
			--background: #f3f6f8;
			--surface: #ffffff;
			--ink: #17212b;
			--muted: #6b7785;
			--accent: #087f8c;
			--accent-dark: #075b65;
			--border: #dce4e8;
			--shadow: 0 12px 30px rgba(23, 33, 43, 0.08);
		}

		* {
			box-sizing: border-box;
		}

		body {
			margin: 0;
			background: var(--background);
			color: var(--ink);
			font-family: "Segoe UI", Tahoma, sans-serif;
		}

		.dashboard {
			width: min(1180px, calc(100% - 32px));
			margin: 0 auto;
			padding: 42px 0 56px;
		}

		header {
			display: flex;
			align-items: end;
			justify-content: space-between;
			gap: 24px;
			margin-bottom: 30px;
		}

		h1 {
			margin: 0 0 8px;
			font-size: clamp(2rem, 4vw, 3.2rem);
			letter-spacing: -0.04em;
		}

		.subtitle {
			margin: 0;
			color: var(--muted);
			font-size: 1rem;
		}

		.connection {
			padding: 10px 14px;
			border: 1px solid #b8dfe3;
			border-radius: 999px;
			background: #e7f6f7;
			color: var(--accent-dark);
			font-size: 0.85rem;
			font-weight: 600;
			white-space: nowrap;
		}

		.metrics {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 18px;
			margin-bottom: 26px;
		}

		.metric,
		.table-panel,
		.error {
			border: 1px solid var(--border);
			border-radius: 12px;
			background: var(--surface);
			box-shadow: var(--shadow);
		}

		.metric {
			padding: 22px 24px;
		}

		.metric-label {
			display: block;
			margin-bottom: 10px;
			color: var(--muted);
			font-size: 0.82rem;
			font-weight: 700;
			letter-spacing: 0.08em;
			text-transform: uppercase;
		}

		.metric-value {
			font-size: 2rem;
			font-weight: 700;
		}

		.metric.procedure {
			border-color: #b8dfe3;
			background: #f2fbfc;
		}

		.metric.procedure .metric-label {
			color: var(--accent-dark);
		}

		.metric.procedure .metric-value {
			color: var(--accent-dark);
		}

		.table-panel {
			overflow: hidden;
		}

		.panel-heading {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 22px 24px;
			border-bottom: 1px solid var(--border);
		}

		h2 {
			margin: 0;
			font-size: 1.25rem;
		}

		.panel-heading span {
			color: var(--muted);
			font-size: 0.9rem;
		}

		.table-wrapper {
			overflow-x: auto;
		}

		table {
			width: 100%;
			border-collapse: collapse;
			min-width: 720px;
		}

		th,
		td {
			padding: 14px 24px;
			border-bottom: 1px solid #edf1f3;
			text-align: left;
		}

		th {
			background: #f8fafb;
			color: var(--muted);
			font-size: 0.76rem;
			letter-spacing: 0.06em;
			text-transform: uppercase;
		}

		td {
			font-size: 0.94rem;
		}

		tbody tr:hover {
			background: #f5fbfb;
		}

		.title {
			font-weight: 700;
		}

		.badge {
			display: inline-block;
			padding: 4px 8px;
			border-radius: 5px;
			background: #e7f6f7;
			color: var(--accent-dark);
			font-size: 0.8rem;
			font-weight: 700;
		}

		.empty,
		.error {
			padding: 24px;
		}

		.error {
			border-color: #f2c5c5;
			background: #fff5f5;
			color: #9a2f2f;
		}

		@media (max-width: 900px) {
			.metrics {
				grid-template-columns: repeat(2, 1fr);
			}
		}

		@media (max-width: 700px) {
			.dashboard {
				width: min(100% - 20px, 1180px);
				padding-top: 24px;
			}

			header {
				align-items: start;
				flex-direction: column;
			}

			.connection {
				white-space: normal;
			}

			.metrics {
				grid-template-columns: 1fr;
			}
		}
	</style>
</head>
<body>
	<main class="dashboard">
		<header>
			<div>
				<h1>Catálogo de películas</h1>
				<p class="subtitle">Consulta de la tabla <strong>film</strong> de la base de datos Sakila</p>
			</div>
			<div class="connection">MySQL · localhost:3306 · sakila</div>
		</header>

		<?php if ($error !== ''): ?>
			<div class="error" role="alert"><?= escapeHtml($error) ?></div>
		<?php else: ?>
			<section class="metrics" aria-label="Resumen del catálogo">
				<article class="metric">
					<span class="metric-label">Películas</span>
					<span class="metric-value"><?= number_format($totalFilms) ?></span>
				</article>
				<article class="metric">
					<span class="metric-label">Valoración media</span>
					<span class="metric-value"><?= number_format((float) $averageRating, 1) ?></span>
				</article>
				<article class="metric">
					<span class="metric-label">Duración media</span>
					<span class="metric-value"><?= number_format((float) $averageLength, 0) ?> min</span>
				</article>
				<article class="metric procedure">
					<span class="metric-label">Total alquileres (procedure)</span>
					<span class="metric-value"><?= number_format($totalAlquileres) ?></span>
				</article>
			</section>

			<section class="table-panel">
				<div class="panel-heading">
					<h2>Listado de films</h2>
					<span><?= number_format($totalFilms) ?> registros</span>
				</div>
				<?php if (count($films) > 0): ?>
					<div class="table-wrapper">
						<table>
							<thead>
								<tr>
									<th>ID</th>
									<th>Título</th>
									<th>Año</th>
									<th>Clasificación</th>
									<th>Duración</th>
									<th>Alquiler</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($films as $film): ?>
									<tr>
										<td><?= escapeHtml($film['film_id']) ?></td>
										<td class="title"><?= escapeHtml($film['title']) ?></td>
										<td><?= escapeHtml($film['release_year']) ?></td>
										<td><span class="badge"><?= escapeHtml($film['rating']) ?></span></td>
										<td><?= escapeHtml($film['length']) ?> min</td>
										<td>$<?= number_format((float) $film['rental_rate'], 2) ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<p class="empty">La tabla film no contiene registros.</p>
				<?php endif; ?>
			</section>
		<?php endif; ?>
	</main>
</body>
</html>