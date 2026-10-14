<?php
/**
 * coleccion.php
 * Muestra la colección del usuario. Los datos se cargan por fetch.
 */
require_once __DIR__ . '/includes/funciones.php';
pedir_login();

$titulo = 'Mi colección';
require __DIR__ . '/includes/cabecera.php';
?>

<section class="panel">
    <h1>Mi colección</h1>

    <div class="progreso">
        <div class="barra"><span id="barra-interior" style="width:0"></span></div>
        <p id="texto-progreso">Cargando…</p>
    </div>

    <div class="filtros">
        <input type="search" id="buscador" class="buscador" placeholder="Buscar por nombre o especie…">
        <select id="orden">
            <option value="id">Orden del catálogo</option>
            <option value="nombre">Nombre (A–Z)</option>
            <option value="copias">Más copias primero</option>
        </select>
    </div>
</section>

<section class="panel">
    <ul class="cartas" id="lista-coleccion"></ul>
    <p id="vacio" class="vacio" hidden>Todavía no tienes cartas. ¡Abre algún sobre!</p>
</section>

<script type="module" src="js/coleccion.js"></script>
<?php require __DIR__ . '/includes/pie.php'; ?>
