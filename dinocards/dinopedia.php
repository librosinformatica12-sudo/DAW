<?php
/**
 * dinopedia.php
 * Catálogo completo de dinosaurios. Se carga por fetch y permite
 * añadir cualquier carta a la colección.
 */
require_once __DIR__ . '/includes/funciones.php';
pedir_login();

$titulo = 'DinoPedia';
require __DIR__ . '/includes/cabecera.php';
?>

<section class="panel">
    <h1>DinoPedia</h1>
    <p>Consulta todos los dinosaurios disponibles y añádelos a tu colección.</p>

    <div class="filtros">
        <input type="search" id="buscador" class="buscador" placeholder="Buscar por nombre o especie…">
        <select id="filtro-periodo">
            <option value="">Todos los periodos</option>
        </select>
    </div>
</section>

<section class="panel">
    <ul class="cartas" id="lista-dinopedia"></ul>
</section>

<script type="module" src="js/dinopedia.js"></script>
<?php require __DIR__ . '/includes/pie.php'; ?>
