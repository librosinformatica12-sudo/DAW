<?php
session_start();
// Si ya hay sesión iniciada, no tiene sentido ver el login
if (isset($_SESSION['usuario_id'])) { header('Location: inicio.php'); exit; }
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
  --deep:#0c1a12; --fern:#1d3b2a; --bone:#ece4cf; --bone-dim:#b9b6a3; --amber:#e3a21a; --ink:#22251f;
  --display:'Bricolage Grotesque','Trebuchet MS',sans-serif; --body:'IBM Plex Sans',system-ui,sans-serif;
}
*{box-sizing:border-box;margin:0}
body{font-family:var(--body);color:var(--bone);min-height:100vh;display:flex;flex-direction:column;
  background:var(--deep);
  background-image:radial-gradient(ellipse at 15% 60%,#24523a 0,transparent 55%),radial-gradient(ellipse at 90% 20%,#13261b 0,transparent 50%)}
/* ---- Barra superior ---- */
header{display:flex;justify-content:space-between;align-items:center;gap:1rem;
  padding:.9rem clamp(1rem,4vw,3.2rem);background:#08120c;border-bottom:2px solid var(--amber)}
.brand{display:flex;align-items:center;gap:.8rem}
.brand svg{width:34px;height:34px;color:var(--bone)}
.brand strong{font-family:var(--display);font-weight:800;font-size:1.6rem;color:var(--amber);line-height:1}
.brand sup{font-size:.7rem;color:var(--bone-dim);margin-left:.4rem;font-weight:500}
.brand small{display:block;font-size:.72rem;letter-spacing:.18em;color:var(--bone-dim);margin-top:.15rem}
.top-right{display:flex;align-items:center;gap:.6rem;font-size:.95rem}
.top-right svg{width:26px;height:26px;color:var(--bone)}
/* ---- Cuerpo ---- */
.stage{flex:1;display:grid;grid-template-columns:1fr minmax(340px,480px);gap:clamp(2rem,8vw,9rem);
  align-items:center;max-width:1280px;width:100%;margin:0 auto;padding:clamp(2rem,6vw,4rem) clamp(1rem,4vw,3rem)}
.hero h1{font-family:var(--display);font-weight:800;font-size:clamp(2.2rem,5vw,3.4rem);line-height:1.05;letter-spacing:-.02em;margin-bottom:1.2rem}
.hero h1 span{display:block;color:var(--amber)}
.hero .lead{color:var(--bone-dim);line-height:1.6;max-width:52ch;margin-bottom:2rem}
.features{list-style:none;padding:0;display:grid;gap:1.1rem}
.features li{display:flex;gap:1rem;align-items:flex-start}
.features svg{flex:none;width:30px;height:30px;color:var(--amber);margin-top:2px}
.features b{display:block;font-family:var(--display);font-size:1.05rem}
.features span{font-size:.9rem;color:var(--bone-dim)}
/* ---- Tarjeta de acceso ---- */
.panel{background:#f7f5ee;color:var(--ink);border-radius:10px;padding:1.8rem 2rem 1.5rem;box-shadow:0 30px 60px -25px #000d}
.tabs{display:grid;grid-template-columns:1fr 1fr;border-bottom:2px solid #dcd8c8;margin-bottom:1.5rem}
.tabs button{font:600 .98rem var(--body);padding:.7rem;border:0;background:none;color:#6d6f60;cursor:pointer;margin-bottom:-2px;border-bottom:2px solid transparent}
.tabs button[aria-selected="true"]{color:var(--ink);border-bottom-color:var(--amber)}
h2{font-family:var(--display);font-size:1.55rem;margin-bottom:1rem}
form{display:flex;flex-direction:column;gap:.9rem}
.field{position:relative}
.field svg{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);width:20px;height:20px;color:#6d6f60;pointer-events:none}
.field input{width:100%;font:400 1rem var(--body);padding:.85rem .9rem .85rem 2.7rem;border:1.5px solid #d3cfbd;border-radius:6px;background:#fff;color:var(--ink)}
.field input::placeholder{color:#7b7d6e}
input:focus-visible,button:focus-visible,a:focus-visible{outline:3px solid var(--amber);outline-offset:2px}
.row{display:flex;justify-content:space-between;align-items:center;font-size:.88rem}
.row label{display:flex;align-items:center;gap:.45rem;cursor:pointer}
.row a{color:#8a5b00;text-decoration:underline;text-underline-offset:3px}
.primary{font:700 1rem var(--body);padding:.9rem;border:0;border-radius:6px;background:var(--amber);color:#2a1e00;cursor:pointer;transition:filter .2s}
.primary:hover{filter:brightness(1.08)}
.primary:disabled{opacity:.6;cursor:wait}
.msg{min-height:1.2em;font-size:.9rem;font-weight:500;color:#9b2a12}
.msg.ok{color:var(--fern)}
.or{display:flex;align-items:center;gap:.8rem;color:#7b7d6e;font-size:.85rem;margin:.6rem 0}
.or::before,.or::after{content:"";flex:1;height:1px;background:#d3cfbd}
.secondary{display:flex;justify-content:center;align-items:center;gap:.5rem;font:600 .92rem var(--body);padding:.85rem;border:1.5px solid #d3cfbd;border-radius:6px;background:#fff;color:var(--ink);cursor:pointer}
.secondary svg{width:18px;height:18px}
.secondary:hover{border-color:var(--amber)}
.note{text-align:center;font-size:.8rem;color:#6d6f60;margin-top:1.2rem}
[hidden]{display:none!important}
@media (max-width:860px){.stage{grid-template-columns:1fr;gap:2.5rem}.top-right span{display:none}}
</style>
</head>
<body>

<header>
  <div class="brand">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="2.5" width="14" height="19" rx="2.500"/><path d="M8 15c1.500-4 3.500-6 7-6-1 2-1 3.500-3 5l-.5 3"/></svg>
    <div><strong>DinoCards</strong><sup>v1.0</sup><small>CLUB DE COLECCIONISTAS</small></div>
  </div>
  <div class="top-right"><span>Portal del Coleccionista</span>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="2.5" width="14" height="19" rx="2.500"/><path d="M12 8v8M8 12h8"/></svg>
  </div>
</header>

<div class="stage">
  <section class="hero">
    <h1>Bienvenido al <span>Portal del Coleccionista</span></h1>
    <p class="lead">Reclama una carta nueva cada día, consulta tu colección y descubre las estadísticas de cada dinosaurio... todo desde un solo lugar.</p>
    <ul class="features">
      <li>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="2.5" width="14" height="19" rx="2.500"/><path d="M12 8v8M8 12h8"/></svg>
        <div><b>Carta diaria</b><span>Abre tu sobre y consigue un dinosaurio nuevo.</span></div>
      </li>
      <li>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="6" width="11" height="15" rx="2"/><path d="M8 3h11a2 2 0 0 1 2 2v12"/></svg>
        <div><b>Mi colección</b><span>Consulta las cartas que ya tienes.</span></div>
      </li>
      <li>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 5.500A2.500 2.500 0 0 1 6.500 3H20v16H6.500A2.500 2.500 0 0 0 4 21.500z"/><path d="M4 21.500V5.500M9 8h7M9 12h7"/></svg>
        <div><b>DinoPedia</b><span>Explora el catálogo completo de especies.</span></div>
      </li>
    </ul>
  </section>

  <main class="panel">
    <div class="tabs" role="tablist">
      <button role="tab" id="tab-login" aria-selected="true" aria-controls="form-login">Iniciar sesión</button>
      <button role="tab" id="tab-registro" aria-selected="false" aria-controls="form-registro">Registrarse</button>
    </div>

    <!-- LOGIN -->
    <form id="form-login" role="tabpanel" aria-labelledby="tab-login" action="auth/login.php" novalidate>
      <h2>Iniciar sesión</h2>
      <div class="field">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.500 3.500-7 8-7s8 2.500 8 7"/></svg>
        <input name="username" placeholder="Usuario" aria-label="Usuario" autocomplete="username" required>
      </div>
      <div class="field">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
        <input type="password" name="password" placeholder="Contraseña" aria-label="Contraseña" autocomplete="current-password" required>
      </div>
      <div class="row">
        <label><input type="checkbox" name="recordarme"> Recordarme</label>
        <a href="#">¿Olvidaste tu contraseña?</a>
      </div>
      <p class="msg" role="alert"></p>
      <button class="primary" type="submit">Iniciar sesión</button>
      <div class="or">o</div>
      <button class="secondary" type="button" data-ir="registro">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10" cy="8" r="4"/><path d="M3 21c0-4 3-6.500 7-6.500M18 14v6M15 17h6"/></svg>
        Crear nueva cuenta
      </button>
    </form>

    <!-- REGISTRO -->
    <form id="form-registro" role="tabpanel" aria-labelledby="tab-registro" action="auth/registro.php" novalidate hidden>
      <h2>Crear cuenta</h2>
      <div class="field">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.500 3.500-7 8-7s8 2.500 8 7"/></svg>
        <input name="username" placeholder="Usuario" aria-label="Usuario" autocomplete="username" required>
      </div>
      <div class="field">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
        <input type="email" name="email" placeholder="Correo electrónico" aria-label="Correo electrónico" autocomplete="email" required>
      </div>
      <div class="field">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
        <input type="password" name="password" placeholder="Contraseña (mín. 6 caracteres)" aria-label="Contraseña" autocomplete="new-password" required minlength="6">
      </div>
      <div class="field">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
        <input type="password" name="confirmPassword" placeholder="Repite la contraseña" aria-label="Repite la contraseña" autocomplete="new-password" required>
      </div>
      <p class="msg" role="alert"></p>
      <button class="primary" type="submit">Crear cuenta</button>
      <div class="or">o</div>
      <button class="secondary" type="button" data-ir="login">Ya tengo cuenta</button>
    </form>

    <p class="note">Una carta nueva te espera cada día en DinoCards.</p>
  </main>
</div>

<script>
const DESTINO = 'inicio.php';
const tabs = {login: document.getElementById('tab-login'), registro: document.getElementById('tab-registro')};
const forms = {login: document.getElementById('form-login'), registro: document.getElementById('form-registro')};

function mostrar(nombre){
  for (const k in tabs){
    tabs[k].setAttribute('aria-selected', k === nombre);
    forms[k].hidden = k !== nombre;
  }
}
tabs.login.onclick = () => mostrar('login');
tabs.registro.onclick = () => mostrar('registro');
document.querySelectorAll('[data-ir]').forEach(b => b.onclick = () => mostrar(b.dataset.ir));

function mensaje(form, texto, ok){
  const p = form.querySelector('.msg');
  p.textContent = texto;
  p.classList.toggle('ok', !!ok);
}

for (const form of Object.values(forms)){
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const datos = new FormData(form);
    const boton = form.querySelector('.primary');

    for (const [k, v] of datos) if (k !== 'recordarme' && !String(v).trim()) return mensaje(form, 'Rellena todos los campos.');
    if (datos.has('confirmPassword') && datos.get('password') !== datos.get('confirmPassword'))
      return mensaje(form, 'Las contraseñas no coinciden.');

    boton.disabled = true;
    mensaje(form, '');
    try {
      const resp = await fetch(form.action, {method:'POST', body:datos, credentials:'same-origin'});
      const json = await resp.json().catch(() => ({}));
      if (!resp.ok || json.error) throw new Error(json.error || 'No se pudo completar la acción.');
      mensaje(form, 'Listo, entrando…', true);
      location.href = DESTINO;
    } catch (err){
      mensaje(form, err.message);
      boton.disabled = false;
    }
  });
}
</script>
</body>
</html>
