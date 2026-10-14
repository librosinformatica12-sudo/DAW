<?php
/**
 * perfil.php
 * Ficha del usuario y estadísticas de su colección (renderizado en servidor).
 */
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/includes/conexion.php';
pedir_login();

$uid = usuario_actual();

// Datos del usuario
$stmt = $pdo->prepare('SELECT username, email, fecha_registro, ultima_obtencion
                       FROM usuarios WHERE id = :id');
$stmt->execute([':id' => $uid]);
$usuario = $stmt->fetch();

// Estadísticas de la colección
$stmt = $pdo->prepare('SELECT COUNT(*) AS cartas, COUNT(DISTINCT dinosaurio_id) AS especies
                       FROM colecciones WHERE usuario_id = :u');
$stmt->execute([':u' => $uid]);
$stats = $stmt->fetch();

$total_catalogo = (int) $pdo->query('SELECT COUNT(*) FROM dinosaurios')->fetchColumn();
$porcentaje = $total_catalogo > 0
    ? (int) round($stats['especies'] / $total_catalogo * 100)
    : 0;

// Su carta más fuerte (suma de estadísticas)
$stmt = $pdo->prepare(
    'SELECT d.* FROM colecciones c
     JOIN dinosaurios d ON d.id = c.dinosaurio_id
     WHERE c.usuario_id = :u
     ORDER BY (d.hp + d.vigor + d.ataque + d.defensa + d.agilidad) DESC
     LIMIT 1'
);
$stmt->execute([':u' => $uid]);
$mejor = $stmt->fetch();

$formato = static fn (?string $fecha): string
    => $fecha ? date('d/m/Y H:i', strtotime($fecha)) : '—';

$titulo = 'Mi perfil';
require __DIR__ . '/includes/cabecera.php';
?>

<section class="panel perfil">
    <div class="perfil-datos">
        <h1><?= escapar($usuario['username']) ?></h1>
        <dl>
            <dt>Correo</dt>
            <dd><?= escapar($usuario['email']) ?></dd>
            <dt>Registrado el</dt>
            <dd><?= $formato($usuario['fecha_registro']) ?></dd>
            <dt>Último sobre</dt>
            <dd><?= $formato($usuario['ultima_obtencion']) ?></dd>
        </dl>
    </div>

    <img class="avatar"
         src="<?= $mejor ? escapar($mejor['imagen_url']) : 'img/img-sobres/sobre.png' ?>"
         alt="Carta destacada">
</section>

<section class="panel">
    <h2>Estadísticas</h2>
    <ul class="resumen">
        <li><b><?= (int) $stats['cartas'] ?></b><span>cartas en total</span></li>
        <li><b><?= (int) $stats['especies'] ?></b><span>especies distintas</span></li>
        <li><b><?= $porcentaje ?>%</b><span>del catálogo completo</span></li>
    </ul>

    <?php if ($mejor): ?>
        <h2 class="subtitulo">Tu carta más fuerte</h2>
        <p>
            <strong><?= escapar($mejor['nombre']) ?></strong> —
            <?= escapar($mejor['especie']) ?>
            (<?= escapar($mejor['periodo']) ?>)
        </p>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/pie.php'; ?>
