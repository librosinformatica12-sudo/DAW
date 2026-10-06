<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit; }
$usuario = htmlspecialchars($_SESSION['username'] ?? 'coleccionista');
$yaReclamada = ($_SESSION['ultima_obtencion'] ?? null) === date('Y-m-d');
?>
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
:root{
  --fern:#1d3b2a; --fern-deep:#142a1e; --bone:#ece4cf; --bone-dim:#cfc6ad; --amber:#e3a21a; --ink:#22251f;
  --display:'Bricolage Grotesque','Trebuchet MS',sans-serif; --body:'IBM Plex Sans',system-ui,sans-serif;
}
*{box-sizing:border-box;margin:0}
body{font-family:var(--body);background:var(--fern-deep);color:var(--bone);min-height:100vh;
  background-image:radial-gradient(circle at 15% 0,#2c5a40 0,transparent 50%)}
/* Barra superior */
header{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:.8rem;
  padding:.9rem clamp(1rem,4vw,2.5rem);background:#0c1a12;border-bottom:3px solid var(--amber)}
.brand{display:flex;align-items:center;gap:.7rem}
.brand svg{width:30px;height:30px;color:var(--amber)}
.brand strong{font-family:var(--display);font-weight:800;font-size:1.3rem}
.brand span{color:var(--bone-dim);font-size:.95rem}
.user{display:flex;align-items:center;gap:1rem;font-size:.95rem;color:var(--bone-dim)}
.user a{color:var(--bone);font-weight:600;text-decoration:underline;text-underline-offset:3px}
a:focus-visible,button:focus-visible{outline:3px solid var(--amber);outline-offset:3px}
/* Contenido */
main{max-width:760px;margin:0 auto;padding:clamp(2rem,6vw,4rem) clamp(1rem,4vw,2rem)}
h1{font-family:var(--display);font-weight:800;font-size:clamp(2rem,5vw,3rem);line-height:1.05;letter-spacing:-.02em;margin-bottom:1rem}
.intro{color:var(--bone-dim);line-height:1.6;max-width:60ch;margin-bottom:2.2rem}
ul{list-style:none;padding:0;display:grid;gap:.9rem}
.item{display:flex;align-items:center;gap:1.1rem;width:100%;text-align:left;font:inherit;color:var(--ink);
  background:var(--bone);border:0;border-radius:14px;padding:1.1rem 1.3rem;text-decoration:none;cursor:pointer;
  transition:transform .15s,box-shadow .15s}
.item:hover{transform:translateX(6px);box-shadow:-6px 0 0 var(--amber)}
.item:disabled{cursor:default;opacity:.7;transform:none;box-shadow:none}
.ico{flex:none;width:52px;height:52px;border-radius:12px;background:var(--fern);color:var(--amber);display:grid;place-items:center}
.ico svg{width:28px;height:28px}
.item b{display:block;font-family:var(--display);font-size:1.25rem;margin-bottom:.15rem}
.item small{font-size:.92rem;color:#4a4d40}
#resultado{margin-top:1.4rem;min-height:1.5em;font-weight:600;color:var(--amber)}
#resultado.error{color:#ffb4a2}
@media (prefers-reduced-motion:reduce){.item{transition:none}}
</style>
</head>
<body>

<header>
  <div class="brand">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="2.5" width="14" height="19" rx="2.5"/><path d="M8 15c1.5-4 3.5-6 7-6-1 2-1 3.500-3 5l-.5 3"/></svg>
    <div><strong>DinoCards</strong> <span>v1.0 · Portal del Coleccionista</span></div>
  </div>
  <div class="user">Sesión de <b><?= $usuario ?></b> <a href="auth/logout.php">Cerrar sesión</a></div>
</header>

<main>
  <h1>Bienvenido al Portal del Coleccionista, <?= $usuario ?></h1>
  <p class="intro">Reclama tu carta diaria, revisa los dinosaurios que ya tienes y explora el catálogo completo, todo desde un solo lugar.</p>

  <ul>
    <li>
      <button class="item" id="btn-diaria" <?= $yaReclamada ? 'disabled' : '' ?>>
        <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="2.5" width="14" height="19" rx="2.5"/><path d="M12 8v8M8 12h8"/></svg></span>
        <span><b>Carta diaria</b><small id="txt-diaria"><?= $yaReclamada ? 'Ya has reclamado tu carta de hoy. Vuelve mañana.' : 'Abre tu sobre de hoy y consigue un dinosaurio nuevo.' ?></small></span>
      </button>
    </li>
    <li>
      <a class="item" href="coleccion.php">
        <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="6" width="11" height="15" rx="2"/><path d="M8 3h11a2 2 0 0 1 2 2v12"/></svg></span>
        <span><b>Mi colección</b><small>Consulta las cartas que has conseguido.</small></span>
      </a>
    </li>
    <li>
      <a class="item" href="dinopedia.php">
        <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 5.500A2.500 2.500 0 0 1 6.500 3H20v16H6.500A2.500 2.500 0 0 0 4 21.500z"/><path d="M4 21.500V5.500M9 8h7M9 12h7"/></svg></span>
        <span><b>DinoPedia</b><small>Explora el catálogo y las estadísticas de cada especie.</small></span>
      </a>
    </li>
  </ul>

  <p id="resultado" role="status"></p>
</main>

<script>
const btn = document.getElementById('btn-diaria');
const res = document.getElementById('resultado');

btn.addEventListener('click', async () => {
  btn.disabled = true;
  res.className = '';
  try {
    const r = await fetch('cartas/reclamar_diaria.php', {credentials: 'same-origin'});
    const j = await r.json();
    if (!r.ok || j.error) throw new Error(j.error || 'No se pudo reclamar la carta.');
    res.textContent = `¡Te ha tocado ${j.dino.nombre} (${j.dino.especie})! Poder total: ${Number(j.poder).toFixed(1)}`;
    document.getElementById('txt-diaria').textContent = 'Carta de hoy reclamada. Vuelve mañana.';
  } catch (e) {
    res.textContent = e.message;
    res.className = 'error';
    if (!/hoy/i.test(e.message)) btn.disabled = false;
  }
});
</script>
</body>
</html>
