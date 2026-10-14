<?php
/**
 * inicio.php
 * Panel principal. Carga el resumen con fetch y abre los sobres.
 */
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/includes/conexion.php';
pedir_login();

$uid = usuario_actual();

$stmt = $pdo->prepare('SELECT username, ultima_obtencion FROM usuarios WHERE id = :id');
$stmt->execute([':id' => $uid]);
$usuario = $stmt->fetch();

$hoy = date('Y-m-d');
$ya_cogida = $usuario['ultima_obtencion'] !== null
    && substr($usuario['ultima_obtencion'], 0, 10) === $hoy;

$titulo = 'Inicio';
require __DIR__ . '/includes/cabecera.php';
?>

<section class="hero">
    <h1>Hola, <span><?= escapar($usuario['username']) ?></span></h1>
    <p>Hoy es <?= date('d/m/Y') ?>. Abre tu sobre del día y sigue completando tu colección.</p>
</section>

<div class="grid-sobres">
    <article class="sobre">
        <h2>Sobre del día</h2>
        <p>Consigue un dinosaurio nuevo gratis cada día.</p>
        <button id="btn-diario" class="boton" data-cogida="<?= $ya_cogida ? '1' : '0' ?>"
                <?= $ya_cogida ? 'disabled' : '' ?>>
            <?= $ya_cogida ? 'Ya abierto hoy' : 'Abrir sobre del día' ?>
        </button>
        <p class="estado" id="estado-diario"></p>
    </article>

    <article class="sobre">
        <h2>Sobre de prueba</h2>
        <p>Ábrelo cuando quieras y consigue 4 cartas de golpe.</p>
        <button id="btn-prueba" class="boton secundario">Abrir sobre de prueba</button>
        <p class="estado" id="estado-prueba"></p>
    </article>
</div>

<section class="panel">
    <h2>Resumen de tu colección</h2>
    <ul class="resumen">
        <li><b id="r-cartas">–</b><span>cartas guardadas</span></li>
        <li><b id="r-especies">–</b><span>especies distintas</span></li>
        <li><b id="r-porcentaje">–</b><span>del catálogo</span></li>
    </ul>
</section>

<section class="panel" id="resultado" hidden>
    <h2 id="resultado-titulo">Cartas obtenidas</h2>
    <ul class="cartas" id="lista-resultado"></ul>
</section>

<script type="module" src="js/inicio.js"></script>
<?php require __DIR__ . '/includes/pie.php'; ?>
