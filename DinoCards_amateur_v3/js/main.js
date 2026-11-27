const accessView = document.getElementById('access-view');
const loginTab = document.getElementById('login-tab');
const registerTab = document.getElementById('register-tab');
const loginPanel = document.getElementById('login-panel');
const registerPanel = document.getElementById('register-panel');
const passwordField = document.getElementById('register-password');
const confirmPasswordField = document.getElementById('confirm-password');
const btnRegistrar = document.getElementById('btn-registrar');
const btnLogin = document.getElementById('btn-login');
const btnLogout = document.getElementById('btn-logout');
const btnTrade = document.getElementById('btn_trade');
const collectionView = document.getElementById('collection-view');
const speciesList = document.getElementById('species-list');
const pColeccionVacia = document.getElementById('p-coleccion-vacia');
const collectionCount = document.getElementById('collection-count');
const dinosaurSearch = document.getElementById('dinosaur-search');
const btnObtenerCarta = document.getElementById('btn-obtenerCarta');
const tradingView = document.getElementById('trading-view');
const listaUsuariosTrade = document.getElementById('lista-usuarios-trade');
const mensajes = document.getElementById('mensajes');
const feedback = document.getElementById('obtain-feedback');
const cooldownText = document.getElementById('obtain-cooldown');

function leerDatos(clave, valorPorDefecto) {
    try {
        return JSON.parse(document.body.dataset[clave] || '') ?? valorPorDefecto;
    } catch (error) {
        return valorPorDefecto;
    }
}

let usuario = leerDatos('usuario', null);
let coleccion = leerDatos('coleccion', []);
let listaTrade = leerDatos('tradeUsers', []);
let speciesItems = [];

function ocultarTodo() {
    accessView.hidden = true;
    collectionView.hidden = true;
    tradingView.hidden = true;
}

function mostrar(panelName) {
    ocultarTodo();

    if (panelName === 'login' || panelName === 'register') {
        const esLogin = panelName === 'login';
        accessView.hidden = false;
        loginTab.classList.toggle('is-active', esLogin);
        registerTab.classList.toggle('is-active', !esLogin);
        loginPanel.hidden = !esLogin;
        registerPanel.hidden = esLogin;
        return;
    }

    if (panelName === 'area-coleccion') {
        collectionView.hidden = false;
        cargarColeccion();
        establecerPermisoObtencion();
        return;
    }

    if (panelName === 'area-trading') {
        tradingView.hidden = false;
        renderUsuariosTrade();
    }
}

function ponerMensaje(texto) {
    mensajes.textContent = texto || 'No hay alertas';
}

async function pedir(url, opciones = {}) {
    const respuesta = await fetch(url, opciones);
    let datos;
    try {
        datos = await respuesta.json();
    } catch (error) {
        throw new Error('El servidor no devolvió una respuesta válida.');
    }
    if (!respuesta.ok || datos.status === 'error' || datos.ok === false) {
        throw new Error(datos.mensaje || 'No se pudo completar la petición.');
    }
    return datos;
}

function validarPasswords() {
    const coinciden = passwordField.value === confirmPasswordField.value;
    confirmPasswordField.setCustomValidity(coinciden ? '' : 'Las contraseñas deben coincidir.');
    return coinciden;
}

function resetearVisor() {
    document.getElementById('dinosaur-art').textContent = '—';
    document.getElementById('viewer-index').textContent = '00 / 00';
    document.getElementById('viewer-order').textContent = 'DINO FILE · —';
    document.getElementById('viewer-family').textContent = 'CARTA DE COLECCIÓN';
    document.getElementById('viewer-name').textContent = 'Sin carta seleccionada';
    document.getElementById('viewer-period').textContent = '—';
    document.getElementById('stats-species').textContent = '—';
    ['stat-height', 'stat-length', 'stat-weight', 'stat-period', 'stat-hp', 'stat-vigor', 'stat-attack', 'stat-defense', 'stat-agility'].forEach((id) => {
        document.getElementById(id).textContent = '—';
    });
}

function seleccionarCarta(button, carta, index, total) {
    speciesItems.forEach((item) => item.button.classList.toggle('is-selected', item.button === button));

    const nombre = carta.nombre || `Carta #${carta.id}`;
    const dinosaurArt = document.getElementById('dinosaur-art');
    dinosaurArt.replaceChildren();
    if (typeof carta.image_url === 'string' && carta.image_url.trim()) {
        const imagen = document.createElement('img');
        imagen.src = `res/${encodeURIComponent(carta.image_url.trim())}`;
        imagen.alt = nombre;
        imagen.addEventListener('error', () => {
            dinosaurArt.textContent = '◇';
        }, { once: true });
        dinosaurArt.append(imagen);
    } else {
        dinosaurArt.textContent = '◇';
    }

    document.getElementById('viewer-order').textContent = `DINO FILE · ${carta.id}`;
    document.getElementById('viewer-index').textContent = `${String(index + 1).padStart(2, '0')} / ${String(total).padStart(2, '0')}`;
    document.getElementById('viewer-family').textContent = carta.especie || 'ESPECIE SIN ESPECIFICAR';
    document.getElementById('viewer-name').textContent = nombre;
    document.getElementById('viewer-period').textContent = carta.periodo || '—';
    document.getElementById('stats-species').textContent = carta.especie || nombre;
    document.getElementById('stat-height').textContent = carta.altura ?? '—';
    document.getElementById('stat-length').textContent = carta.largo ?? '—';
    document.getElementById('stat-weight').textContent = carta.peso ?? '—';
    document.getElementById('stat-period').textContent = carta.periodo || '—';
    document.getElementById('stat-hp').textContent = carta.hp ?? '—';
    document.getElementById('stat-vigor').textContent = carta.vigor ?? '—';
    document.getElementById('stat-attack').textContent = carta.ataque ?? '—';
    document.getElementById('stat-defense').textContent = carta.defensa ?? '—';
    document.getElementById('stat-agility').textContent = carta.agilidad ?? '—';
}

function aplicarFiltro() {
    const termino = (dinosaurSearch?.value || '').trim().toLocaleLowerCase('es');
    let visibles = 0;

    speciesItems.forEach(({ button, carta }) => {
        const texto = `${carta.nombre || ''} ${carta.especie || ''}`.toLocaleLowerCase('es');
        const coincide = texto.includes(termino);
        button.hidden = !coincide;
        if (coincide) visibles += 1;
    });

    pColeccionVacia.hidden = visibles > 0;
    pColeccionVacia.textContent = speciesItems.length
        ? 'No hay cartas que coincidan.'
        : 'No hay cartas en tu colección.';
}

function cargarColeccion() {
    const cartas = Array.isArray(coleccion)
        ? coleccion
            .filter((carta) => carta && Number.isSafeInteger(Number(carta.id)) && Number(carta.id) > 0)
            .map((carta) => ({ ...carta, id: Number(carta.id) }))
        : [];

    speciesList.querySelectorAll('.species-item').forEach((item) => item.remove());
    speciesItems = [];
    collectionCount.textContent = String(cartas.length).padStart(2, '0');

    cartas.forEach((carta, index) => {
        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'species-item';
        boton.dataset.cardId = String(carta.id);

        const icono = document.createElement('span');
        icono.className = 'species-icon';
        icono.textContent = '◇';

        const nombre = document.createElement('span');
        nombre.className = 'species-name';
        nombre.textContent = carta.nombre || `Carta #${carta.id}`;

        const detalle = document.createElement('small');
        detalle.textContent = `${carta.especie || 'Especie sin especificar'} · #${carta.id}`;
        nombre.append(detalle);

        const flecha = document.createElement('span');
        flecha.className = 'species-arrow';
        flecha.textContent = '›';

        boton.append(icono, nombre, flecha);
        boton.addEventListener('click', () => seleccionarCarta(boton, carta, index, cartas.length));
        speciesList.insertBefore(boton, pColeccionVacia);
        speciesItems.push({ button: boton, carta });
    });

    if (speciesItems.length) {
        seleccionarCarta(speciesItems[0].button, speciesItems[0].carta, 0, speciesItems.length);
    } else {
        resetearVisor();
    }
    aplicarFiltro();
}

function establecerPermisoObtencion() {
    const ultima = usuario?.fechaHoraUltimaObtencionCarta;
    if (!ultima) {
        btnObtenerCarta.disabled = false;
        if (cooldownText) cooldownText.textContent = 'Ya puedes obtener otra carta.';
        return;
    }

    const fecha = new Date(String(ultima).replace(' ', 'T'));
    if (Number.isNaN(fecha.getTime())) {
        btnObtenerCarta.disabled = false;
        if (cooldownText) cooldownText.textContent = 'No se pudo determinar cuándo podrás obtener otra carta.';
        return;
    }

    const horas = (Date.now() - fecha.getTime()) / (1000 * 60 * 60);
    if (horas < 24) {
        btnObtenerCarta.disabled = true;
        const restante = (new Date(fecha.getTime() + 24 * 60 * 60 * 1000).getTime() - Date.now()) / 60000;
        const minutos = Math.max(1, Math.ceil(restante));
        const horasRestantes = Math.floor(minutos / 60);
        const minutosRestantes = minutos % 60;
        const partes = [];
        if (horasRestantes) partes.push(`${horasRestantes} hora${horasRestantes === 1 ? '' : 's'}`);
        if (minutosRestantes) partes.push(`${minutosRestantes} minuto${minutosRestantes === 1 ? '' : 's'}`);
        if (cooldownText) cooldownText.textContent = `Podrás obtener otra carta en ${partes.join(' y ')}.`;
    } else {
        btnObtenerCarta.disabled = false;
        if (cooldownText) cooldownText.textContent = 'Ya puedes obtener otra carta.';
    }
}

function renderUsuariosTrade() {
    if (!listaUsuariosTrade) return;
    listaUsuariosTrade.replaceChildren();
    if (!Array.isArray(listaTrade) || !listaTrade.length) {
        const parrafo = document.createElement('p');
        parrafo.className = 'empty-species';
        parrafo.textContent = 'No hay exploradores listos para negociar.';
        listaUsuariosTrade.append(parrafo);
        return;
    }

    listaTrade.forEach((otro) => {
        const enlace = document.createElement('a');
        enlace.className = 'submit-button';
        enlace.href = 'intercambiar.php';
        enlace.textContent = `${otro.username || 'Explorador'} ↗`;
        listaUsuariosTrade.append(enlace);
    });
}

loginTab.addEventListener('click', () => mostrar('login'));
registerTab.addEventListener('click', () => mostrar('register'));
passwordField.addEventListener('input', validarPasswords);
confirmPasswordField.addEventListener('input', validarPasswords);
if (dinosaurSearch) dinosaurSearch.addEventListener('input', aplicarFiltro);

btnRegistrar.addEventListener('click', async () => {
    if (!validarPasswords()) return;

    const datos = new FormData();
    datos.append('username', document.getElementById('register-username').value);
    datos.append('email', document.getElementById('register-email').value);
    datos.append('password', passwordField.value);
    datos.append('confirmPassword', confirmPasswordField.value);

    try {
        const respuesta = await pedir('backend.php?accion=registro', { method: 'POST', body: datos });
        ponerMensaje(`${respuesta.status} · Código: ${respuesta.resultado} · ${respuesta.mensaje}`);
        mostrar('login');
    } catch (error) {
        ponerMensaje(error.message);
    }
});

btnLogin.addEventListener('click', async () => {
    const datos = new FormData();
    datos.append('email', document.getElementById('login-email').value);
    datos.append('password', document.getElementById('login-password').value);

    try {
        const respuesta = await pedir('backend.php?accion=login', { method: 'POST', body: datos });
        usuario = respuesta.usuario;
        coleccion = Array.isArray(respuesta.coleccion) ? respuesta.coleccion : [];
        document.getElementById('explorer-name').textContent = usuario?.username || 'Explorador';
        ponerMensaje(`${respuesta.status} · Código: ${respuesta.resultado} · ${respuesta.mensaje}`);
        feedback.textContent = '';
        mostrar('area-coleccion');
    } catch (error) {
        ponerMensaje(error.message);
    }
});

btnLogout.addEventListener('click', async () => {
    try {
        await pedir('backend.php?accion=logout', { method: 'POST' });
    } catch (error) {
        ponerMensaje(error.message);
    } finally {
        location.reload();
    }
});

btnObtenerCarta.addEventListener('click', async () => {
    if (btnObtenerCarta.disabled) return;

    try {
        const respuesta = await pedir('backend.php?accion=anadir-carta', { method: 'POST' });
        usuario = respuesta.usuario || usuario;
        coleccion = Array.isArray(respuesta.coleccion) ? respuesta.coleccion : [];
        ponerMensaje(`${respuesta.status} · Código: ${respuesta.resultado} · ${respuesta.mensaje}`);
        feedback.textContent = respuesta.mensaje;
        cargarColeccion();
        establecerPermisoObtencion();
    } catch (error) {
        feedback.textContent = error.message;
        ponerMensaje(error.message);
    }
});

btnTrade.addEventListener('click', async () => {
    try {
        const respuesta = await pedir('backend.php?accion=entrar-a-trade');
        listaTrade = Array.isArray(respuesta.lista_usuarios_trade) ? respuesta.lista_usuarios_trade : [];
        ponerMensaje(`${respuesta.status} · ${respuesta.mensaje}`);
        mostrar('area-trading');
    } catch (error) {
        ponerMensaje(error.message);
    }
});

if (usuario) {
    document.getElementById('explorer-name').textContent = usuario.username || 'Explorador';
}
cargarColeccion();
establecerPermisoObtencion();
renderUsuariosTrade();
mostrar(usuario ? 'area-coleccion' : 'login');
