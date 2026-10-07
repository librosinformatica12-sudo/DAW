<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit; }

function e($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

$usuario = $_SESSION['username'] ?? $_SESSION['usuario'] ?? 'coleccionista';
$ultima = $_SESSION['ultima_obtencion'] ?? null;
$yaReclamada = $ultima && substr((string) $ultima, 0, 10) === date('Y-m-d');

$coleccion = []; $catalogo = []; $errorDatos = null;
try {
    require_once __DIR__ . '/src/AccesoDatos.php';

    if (function_exists('PA_MiColeccion')) {
        $coleccion = PA_MiColeccion((int) $_SESSION['usuario_id']);
    } else {
        $errorDatos = 'La función PA_MiColeccion() no está disponible. Comprueba src/AccesoDatos.php y el SQL de procedimientos.';
    }

    if (function_exists('PA_Catalogo')) {
        $catalogo = PA_Catalogo();
    } elseif ($errorDatos === null) {
        $errorDatos = 'La función PA_Catalogo() no está disponible. Comprueba src/AccesoDatos.php y el SQL de procedimientos.';
    }
} catch (Throwable $ex) {
    $errorDatos = $ex->getMessage();
}
$tengo = array_flip(array_column($coleccion, 'id'));   // ids de dinosaurio que ya posee
$total = count($catalogo);
$distintas = count($tengo);

// Pinta una carta de dinosaurio
function carta(array $d, bool $poseida = true): void {
    $poder = round(($d['hp'] + $d['vigor'] + $d['ataque'] + $d['defensa'] + $d['agilidad']) / 5, 1);
    $stats = ['hp' => 'HP', 'vigor' => 'Vigor', 'ataque' => 'Ataque', 'defensa' => 'Defensa', 'agilidad' => 'Agilidad']; ?>
    <article class="carta<?= $poseida ? '' : ' bloqueada' ?>">
      <header><b><?= e($d['nombre']) ?></b><small><?= e($d['periodo']) ?></small></header>
      <div class="art">🦖<?php if (!empty($d['imagen_url'])): ?><img src="<?= e($d['imagen_url']) ?>" alt="<?= e($d['nombre']) ?>" onerror="this.remove()"><?php endif; ?></div>
      <p class="esp"><?= e($d['especie']) ?></p>
      <dl class="stats">
        <?php foreach ($stats as $k => $n): $v = (int) $d[$k]; ?>
          <dt><?= $n ?></dt><dd><span class="bar"><i style="width:<?= min(100, $v) ?>%"></i></span><em><?= $v ?></em></dd>
        <?php endforeach; ?>
      </dl>
      <footer>Poder total <b><?= $poder ?></b><?= $poseida ? '' : ' · sin descubrir' ?></footer>
    </article>
<?php } ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>DinoCards · Portal del Coleccionista</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--deep:#0c1a12;--fern:#1d3b2a;--bone:#ece4cf;--dim:#b9b6a3;--amber:#e3a21a;--ink:#22251f;
  --display:'Bricolage Grotesque','Trebuchet MS',sans-serif;--body:'IBM Plex Sans',system-ui,sans-serif}
*{box-sizing:border-box;margin:0}
body{font-family:var(--body);background:var(--deep);color:var(--bone);min-height:100vh;
  background-image:radial-gradient(circle at 15% 0,#2c5a40 0,transparent 45%)}
a:focus-visible,button:focus-visible{outline:3px solid var(--amber);outline-offset:3px}
header.top{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:.8rem;
  padding:.9rem clamp(1rem,4vw,2.5rem);background:#08120c;border-bottom:2px solid var(--amber)}
.top strong{font-family:var(--display);font-weight:800;font-size:1.5rem;color:var(--amber)}
.top small{color:var(--dim);margin-left:.4rem}
.top nav{display:flex;align-items:center;gap:1.2rem;font-size:.95rem}
.top nav a{color:var(--bone);text-decoration:none}.top nav a:hover{color:var(--amber)}
.top nav .salir{text-decoration:underline;text-underline-offset:3px;font-weight:600}
main{max-width:1180px;margin:0 auto;padding:clamp(1.5rem,5vw,3rem) clamp(1rem,4vw,2rem)}
h1{font-family:var(--display);font-weight:800;font-size:clamp(1.9rem,4.5vw,2.8rem);line-height:1.05;letter-spacing:-.02em;margin-bottom:.6rem}
h1 span{color:var(--amber)}
h2{font-family:var(--display);font-size:1.5rem;margin:2.6rem 0 1rem;scroll-margin-top:1rem}
.lead{color:var(--dim);max-width:60ch;line-height:1.6}
.resumen{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:1rem;margin:1.8rem 0}
.dato{background:#ffffff0f;border:1px solid #ffffff1c;border-radius:12px;padding:1rem 1.2rem}
.dato b{display:block;font-family:var(--display);font-size:2rem;color:var(--amber);line-height:1.1}
.dato span{font-size:.9rem;color:var(--dim)}
.diaria{display:flex;flex-wrap:wrap;align-items:center;gap:1.2rem;background:var(--bone);color:var(--ink);border-radius:14px;padding:1.3rem 1.5rem}
.diaria div{flex:1;min-width:230px}.diaria b{font-family:var(--display);font-size:1.25rem;display:block}
.diaria small{color:#4a4d40;font-size:.92rem}
.diaria button{font:700 1rem var(--body);padding:.85rem 1.6rem;border:0;border-radius:8px;background:var(--amber);color:#2a1e00;cursor:pointer}
.diaria button:hover:not(:disabled){filter:brightness(1.08)}.diaria button:disabled{opacity:.55;cursor:default}
#resultado{margin-top:.9rem;min-height:1.4em;font-weight:600;color:var(--amber)}#resultado.error{color:#ffb4a2}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1.1rem}
.carta{background:var(--bone);color:var(--ink);border:3px solid var(--amber);border-radius:14px;padding:.7rem;display:flex;flex-direction:column;gap:.5rem}
.carta header{display:flex;justify-content:space-between;align-items:baseline}
.carta header b{font-family:var(--display);font-size:1.15rem}.carta header small{font-size:.75rem;color:#5b5a4c;font-weight:600}
.art{position:relative;aspect-ratio:4/3;border-radius:8px;display:grid;place-items:center;font-size:3.4rem;overflow:hidden;
  background:linear-gradient(180deg,#9fc3a0,#4f8a63)}
.art img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.esp{font-size:.78rem;font-style:italic;color:#4a4d40}
.stats{display:grid;grid-template-columns:auto 1fr;gap:3px .6rem;font-size:.74rem;font-weight:600}
.stats dd{display:flex;align-items:center;gap:.4rem;margin:0}
.bar{flex:1;height:7px;border-radius:4px;background:#1d3b2a22;overflow:hidden}.bar i{display:block;height:100%;background:var(--fern)}
.stats em{font-style:normal;width:1.7rem;text-align:right}
.carta footer{font-size:.8rem;border-top:1px solid #1d3b2a22;padding-top:.45rem}.carta footer b{color:#8a5b00}
.carta.bloqueada{filter:grayscale(1);opacity:.45;border-color:#7b7d6e}
.vacio{color:var(--dim);background:#ffffff0a;border:1px dashed #ffffff30;border-radius:12px;padding:1.4rem}
.error-datos{background:#4a1d12;color:#ffd9cf;border-radius:10px;padding:1rem 1.2rem;margin-bottom:1.5rem}
</style>
</head>
<body>

<header class="top">
  <div><strong>DinoCards</strong><small>v1.0 · Portal del Coleccionista</small></div>
  <nav>
    <a href="#diaria">Carta diaria</a><a href="#coleccion">Mi colección</a><a href="#dinopedia">DinoPedia</a>
    <span>Hola, <b><?= e($usuario) ?></b></span>
    <a class="salir" href="php/logout.php">Cerrar sesión</a>
  </nav>
</header>

<main>
  <?php if ($errorDatos): ?>
    <div class="error-datos" role="alert"><b>No se pudieron cargar los datos:</b> <?= e($errorDatos) ?><br>¿Has ejecutado <code>sql/dinocards_procedimientos.sql</code> y añadido el <code>require_once</code> de <code>AccesoDatos_extra.php</code>?</div>
  <?php endif; ?>

  <h1>Bienvenido, <span><?= e($usuario) ?></span></h1>
  <p class="lead">Reclama una carta nueva cada día, revisa los dinosaurios que ya tienes y explora el catálogo completo.</p>

  <section class="resumen" aria-label="Tu progreso">
    <div class="dato"><b><?= count($coleccion) ?></b><span>cartas en tu colección</span></div>
    <div class="dato"><b><?= $distintas ?> / <?= $total ?></b><span>especies descubiertas</span></div>
    <div class="dato"><b><?= $yaReclamada ? 'Hecha' : 'Libre' ?></b><span>carta de hoy</span></div>
  </section>

  <h2 id="diaria">Carta diaria</h2>
  <section class="diaria">
    <div><b>Abre tu sobre de hoy</b><small id="txt-diaria"><?= $yaReclamada ? 'Ya has reclamado tu carta de hoy. Vuelve mañana.' : 'Consigue un dinosaurio al azar para tu colección.' ?></small></div>
    <button id="btn-diaria" <?= $yaReclamada ? 'disabled' : '' ?>>Reclamar carta</button>
  </section>
  <p id="resultado" role="status"></p>

  <h2 id="coleccion">Mi colección</h2>
  <?php if ($coleccion): ?>
    <div class="grid"><?php foreach ($coleccion as $d) carta($d, true); ?></div>
  <?php else: ?>
    <p class="vacio">Todavía no tienes cartas. Pulsa «Reclamar carta» para conseguir la primera.</p>
  <?php endif; ?>

  <h2 id="dinopedia">DinoPedia</h2>
  <?php if ($catalogo): ?>
    <div class="grid"><?php foreach ($catalogo as $d) carta($d, isset($tengo[$d['id']])); ?></div>
  <?php else: ?>
    <p class="vacio">El catálogo está vacío. Ejecuta el SQL para cargar los dinosaurios de ejemplo.</p>
  <?php endif; ?>
</main>

<script>
const btn = document.getElementById('btn-diaria');
const res = document.getElementById('resultado');

btn.addEventListener('click', async () => {
  btn.disabled = true;
  res.className = '';
  res.textContent = '';
  try {
    const r = await fetch('php/carta_diaria.php', {method: 'POST', credentials: 'same-origin', headers: {'X-Requested-With': 'XMLHttpRequest'}});
    const j = await r.json();
    if (!j.ok) throw new Error(j.mensaje);
    res.textContent = j.mensaje;
    setTimeout(() => location.reload(), 1600);   // recarga para ver la carta nueva
  } catch (err) {
    res.textContent = err.message || 'No se pudo contactar con el servidor.';
    res.className = 'error';
    btn.disabled = false;
  }
});
</script>
</body>
</html>