<?php
session_start();

function PuedeObtenerNuevaCarta(?string $ultimaObtencion, ?int &$tiempoRestante = null): bool
{
    if ($ultimaObtencion === null || trim($ultimaObtencion) === '' || str_starts_with($ultimaObtencion, '0000-00-00')) {
        $tiempoRestante = 0;
        return true;
    }

    try {
        $proximaObtencion = (new DateTimeImmutable($ultimaObtencion))->modify('+24 hours');
    } catch (Throwable $exception) {
        $tiempoRestante = null;
        return false;
    }

    $tiempoRestante = max(0, $proximaObtencion->getTimestamp() - time());
    return $tiempoRestante === 0;
}

$usuarioActual = $_SESSION['usuario'] ?? null;
$estaAutenticado = is_array($usuarioActual);
$nombreUsuario = htmlspecialchars((string) ($usuarioActual['username'] ?? 'Explorador'), ENT_QUOTES, 'UTF-8');

$ultimaObtencion = $usuarioActual['fechaHoraUltimaObtencionCarta'] ?? null;
$tiempoRestante = null;
$puedeObtenerCarta = $ultimaObtencion === null
    ? PuedeObtenerNuevaCarta(null, $tiempoRestante)
    : (is_string($ultimaObtencion) && PuedeObtenerNuevaCarta($ultimaObtencion, $tiempoRestante));

$textoTiempoRestante = '';
if ($tiempoRestante !== null) {
    $minutosRestantes = (int) ceil($tiempoRestante / 60);
    if ($minutosRestantes === 0) {
        $textoTiempoRestante = 'Ya puedes obtener otra carta.';
    } else {
        $horasRestantes = intdiv($minutosRestantes, 60);
        $minutosRestantes %= 60;
        $partesTiempo = [];
        if ($horasRestantes > 0) {
            $partesTiempo[] = $horasRestantes . ($horasRestantes === 1 ? ' hora' : ' horas');
        }
        if ($minutosRestantes > 0) {
            $partesTiempo[] = $minutosRestantes . ($minutosRestantes === 1 ? ' minuto' : ' minutos');
        }
        $textoTiempoRestante = 'Podrás obtener otra carta en ' . implode(' y ', $partesTiempo) . '.';
    }
} elseif ($estaAutenticado) {
    $textoTiempoRestante = 'No se pudo determinar cuándo podrás obtener otra carta.';
}

$coleccionActual = $_SESSION['coleccion'] ?? [];
$coleccionActual = is_array($coleccionActual) ? $coleccionActual : [];
$listaTrade = $_SESSION['lista_usuarios_trade'] ?? [];
$listaTrade = is_array($listaTrade) ? $listaTrade : [];
$mensajeColeccion = (string) ($_SESSION['mensajeColeccion'] ?? '');
unset($_SESSION['mensajeColeccion']);

$flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
$usuarioJson = htmlspecialchars(json_encode($estaAutenticado ? $usuarioActual : null, $flags) ?: 'null', ENT_QUOTES, 'UTF-8');
$coleccionJson = htmlspecialchars(json_encode($coleccionActual, $flags) ?: '[]', ENT_QUOTES, 'UTF-8');
$listaTradeJson = htmlspecialchars(json_encode($listaTrade, $flags) ?: '[]', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#123b2d">
    <meta name="description" content="Entra en DinoCards, reclama tu carta diaria y empieza a completar tu DinoPedia.">
    <title>DinoCards | Tu próxima expedición</title>
    <link rel="stylesheet" href="styles/estilos.css">
    <script src="js/main.js" defer></script>
</head>
<body data-usuario="<?= $usuarioJson ?>" data-coleccion="<?= $coleccionJson ?>" data-trade-users="<?= $listaTradeJson ?>">
    <div class="site-shell">
        <header class="site-header">
            <a class="brand" href="index.php">
                <span class="brand-mark">D</span>
                <span class="brand-name">Dino<span>Cards</span></span>
            </a>
            <p class="header-note"><span class="status-dot"></span>ARCHIVO DE EXPEDICIÓN <span>·</span> v 0.0.3</p>
        </header>

        <main class="app-main">
            <section id="panel-mensajes">
                <div>
                    <p id="mensajes">No hay alertas</p>
                </div>
            </section>

            <section id="access-view" class="landing-layout" <?= $estaAutenticado ? 'hidden' : '' ?>>
                <section class="welcome-panel">
                    <p class="eyebrow"><span class="eyebrow-line"></span>EL REGISTRO FÓSIL TE ESPERA</p>
                    <h1 id="welcome-title">El pasado<br>cobra <span>vida.</span></h1>
                    <p class="welcome-copy">¡Inicia sesión para reclamar tu carta diaria y completar tu DinoPedia!</p>
                    <p class="welcome-detail">Cada carta es una nueva especie. Cada expedición, una historia que añadir a tu colección.</p>
                    <div class="field-note"><span>✳</span> TU COLECCIÓN EMPIEZA CON UN DESCUBRIMIENTO</div>
                </section>
                <section class="access-panel">
                    <div class="access-heading">
                        <p class="eyebrow">IDENTIFICACIÓN DE EXPLORADOR</p>
                        <h2 id="access-heading">Entra en la expedición</h2>
                        <p>Tu próxima carta está a un paso.</p>
                    </div>

                    <div class="auth-box">
                        <div class="auth-tabs">
                            <button id="login-tab" class="auth-tab is-active" type="button">Iniciar sesión</button>
                            <button id="register-tab" class="auth-tab" type="button">Crear cuenta</button>
                        </div>

                        <div class="auth-form" id="login-panel">
                            <div class="form-intro">
                                <span class="form-index">01 / ACCESO</span>
                                <p>Identifica tus credenciales para continuar.</p>
                            </div>
                            <label class="field-label" for="login-email">Correo electrónico</label>
                            <input id="login-email" class="field-input" name="email" type="email" autocomplete="email" placeholder="explorador@dinocards.com" required>
                            <div class="label-row">
                                <label class="field-label" for="login-password">Contraseña</label>
                                <span class="field-hint">ACCESO PERSONAL</span>
                            </div>
                            <input id="login-password" class="field-input" name="password" type="password" autocomplete="current-password" placeholder="Introduce tu contraseña" required>
                            <button id="btn-login" class="submit-button" type="button"><span>Entrar en DinoCards</span><span class="button-arrow">↗</span></button>
                            <p class="form-footnote"><span>◇</span> Tus datos de acceso permanecen protegidos.</p>
                        </div>

                        <div class="auth-form" id="register-panel" hidden>
                            <div class="form-intro">
                                <span class="form-index">02 / NUEVO EXPLORADOR</span>
                                <p>Crea tu perfil y prepara tu primera expedición.</p>
                            </div>
                            <label class="field-label" for="register-username">Nombre de explorador</label>
                            <input id="register-username" class="field-input" name="username" type="text" autocomplete="username" placeholder="Tu nickname" required>
                            <label class="field-label" for="register-email">Correo electrónico</label>
                            <input id="register-email" class="field-input" name="email" type="email" autocomplete="email" placeholder="explorador@dinocards.com" required>
                            <label class="field-label" for="register-password">Contraseña</label>
                            <input id="register-password" class="field-input" name="password" type="password" autocomplete="new-password" placeholder="Crea una contraseña" required>
                            <label class="field-label" for="confirm-password">Confirmar contraseña</label>
                            <input id="confirm-password" class="field-input" name="confirmPassword" type="password" autocomplete="new-password" placeholder="Escríbela de nuevo" required>
                            <button id="btn-registrar" class="submit-button" type="button"><span>Crear mi cuenta</span><span class="button-arrow">↗</span></button>
                            <p class="form-footnote"><span>◇</span> Una cuenta. Una colección. Infinitos hallazgos.</p>
                        </div>
                    </div>
                    <p class="access-footer"><span class="footer-rule"></span> SISTEMA DE COLECCIÓN · EST. 2026 <span class="footer-rule"></span></p>
                </section>
            </section>

            <section id="collection-view" class="collection-view" <?= $estaAutenticado ? '' : 'hidden' ?>>
                <header class="collection-heading">
                    <div>
                        <p class="eyebrow"><span class="eyebrow-line"></span>ARCHIVO PERSONAL</p>
                        <h1 id="collection-title">Tu colección</h1>
                    </div>
                    <div class="explorer-account">
                        <button id="btn_trade" type="button">Intercambiar cartas</button>
                        <p class="explorer-tag"><span class="status-dot"></span><span id="explorer-name"><?= $nombreUsuario ?></span></p>
                        <button id="btn-logout" class="logout-button" type="button">Cerrar sesión</button>
                    </div>
                </header>

                <div class="collection-workspace">
                    <aside class="collection-sidebar">
                        <div class="sidebar-heading">
                            <h2 id="collection-list-title">Especies</h2>
                            <span id="collection-count" class="collection-count">00</span>
                        </div>
                        <label class="search-label" for="dinosaur-search">Buscar dinosaurio</label>
                        <input class="collection-search" id="dinosaur-search" type="search" placeholder="Nombre de especie..." autocomplete="off">
                        <nav id="species-list" class="species-list">
                            <p id="p-coleccion-vacia" class="empty-species">No hay cartas en tu colección.</p>
                        </nav>
                    </aside>

                    <div class="specimen-stage">
                        <p class="stage-label"><span class="status-dot"></span>VISOR DE ESPECÍMENES <span id="viewer-index">00 / 00</span></p>
                        <article class="collection-card">
                            <div class="collection-card-top"><span id="viewer-order">DINO FILE · —</span></div>
                            <div class="dinosaur-art" id="dinosaur-art">—</div>
                            <div class="card-title-row"><div><p class="card-kicker" id="viewer-family">CARTA DE COLECCIÓN</p><h2 id="viewer-name">Sin carta seleccionada</h2></div><span class="card-seal">D</span></div>
                            <div class="card-footer"><span>ARCHIVO DE ESPECIES</span><span id="viewer-period">—</span></div>
                        </article>
                        <button id="btn-obtenerCarta" class="obtain-button" type="button" <?= $puedeObtenerCarta ? '' : 'disabled' ?>><span>Añadir carta aleatoria</span><span>＋</span></button>
                        <p class="obtain-cooldown" id="obtain-cooldown"><?= htmlspecialchars($textoTiempoRestante, ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="obtain-feedback" id="obtain-feedback"><?= htmlspecialchars($mensajeColeccion, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>

                    <aside class="stats-panel">
                        <p class="eyebrow">FICHA TÉCNICA</p>
                        <h2 id="stats-title">Estadísticas</h2>
                        <p class="stats-species" id="stats-species">—</p>
                        <dl class="stats-list">
                            <div><dt>Altura</dt><dd id="stat-height">—</dd></div>
                            <div><dt>Largo</dt><dd id="stat-length">—</dd></div>
                            <div><dt>Peso</dt><dd id="stat-weight">—</dd></div>
                            <div><dt>Época</dt><dd id="stat-period">—</dd></div>
                            <div><dt>HP</dt><dd id="stat-hp">—</dd></div>
                            <div><dt>Vigor</dt><dd id="stat-vigor">—</dd></div>
                            <div><dt>Ataque</dt><dd id="stat-attack">—</dd></div>
                            <div><dt>Defensa</dt><dd id="stat-defense">—</dd></div>
                            <div><dt>Agilidad</dt><dd id="stat-agility">—</dd></div>
                        </dl>
                    </aside>
                </div>
            </section>

            <section id="trading-view" class="trading-view" hidden>
                <div id="collection-trading-view">
                    <div id="users-trading-view" class="users-trading-view">
                        <h2>Usuarios listos para negociar</h2>
                        <div id="lista-usuarios-trade"></div>
                    </div>
                    <aside class="collection-sidebar">
                        <div class="sidebar-heading">
                            <h2>Tu colección</h2>
                        </div>
                    </aside>
                </div>
                <div id="central-trading-view">
                    <div id="mesa-trading"></div>
                    <div id="control-trading">
                        <a id="btn-abrir-intercambio" class="submit-button" href="intercambiar.php">Abrir mesa de intercambio ↗</a>
                    </div>
                </div>
                <div id="chat-trading-view"></div>
            </section>
        </main>

        <footer class="site-footer">
            <span>DINOCARDS <span class="footer-divider">/</span> ARCHIVO DE ESPECIES</span>
            <span>EL JURÁSICO NUNCA TERMINÓ</span>
        </footer>
    </div>
</body>
</html>
