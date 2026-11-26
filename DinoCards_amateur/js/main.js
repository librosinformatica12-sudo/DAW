/* Seccion de Login y Registro */
const loginTab = document.getElementById('login-tab');
const registerTab = document.getElementById('register-tab');
const loginPanel = document.getElementById('login-panel');
const registerPanel = document.getElementById('register-panel');
const passwordField = document.getElementById('register-password');
const confirmPasswordField = document.getElementById('confirm-password');
const registerForm = registerPanel;

/* Seccion de Pantalla principal (coleccion cartas, visor de carta) */
const btnTrade = document.getElementById('btn_trade');
const dinosaurSearch = document.getElementById('dinosaur-search');
const collectionView = document.getElementById('collection-view');
const speciesList = document.getElementById('species-list');
const emptySpecies = document.getElementById('empty-species');
const collectionCount = document.getElementById('collection-count');

function showAuthPanel(panelName) {
    const isLoginActive = panelName === 'login';
    loginTab.classList.toggle('is-active', isLoginActive);
    registerTab.classList.toggle('is-active', !isLoginActive);

    loginPanel.hidden = !isLoginActive;
    registerPanel.hidden = isLoginActive;
}

loginTab.addEventListener('click', () => showAuthPanel('login'));
registerTab.addEventListener('click', () => showAuthPanel('register'));
btnTrade.addEventListener('click', mostrarAreaTrading);
confirmPasswordField.addEventListener('input', validatePasswordMatch);
passwordField.addEventListener('input', validatePasswordMatch);

registerForm.addEventListener('submit', (event) => {
    validatePasswordMatch();
    if (!registerForm.checkValidity()) event.preventDefault();
});
dinosaurSearch.addEventListener('input', () => {
    const searchTerm = dinosaurSearch.value.trim().toLocaleLowerCase('es');
    let visibleCount = 0;

    speciesItems.forEach((item) => {
        const matches = item.textContent.toLocaleLowerCase('es').includes(searchTerm);
        item.hidden = !matches;
        if (matches) visibleCount += 1;
    });

    emptySpecies.hidden = speciesItems.length > 0 && visibleCount > 0;
});


function validatePasswordMatch() {
    const passwordsMatch = passwordField.value === confirmPasswordField.value;
    confirmPasswordField.setCustomValidity(passwordsMatch ? '' : 'Las contraseñas deben coincidir.');
}

let speciesItems = [];

function selectCard(button, card, index, total) {
    speciesItems.forEach((item) => {
        const isSelected = item === button;
        item.classList.toggle('is-selected', isSelected);
    });

    const cardId = card.id;
    const nombre = card.nombre || `Carta #${cardId}`;
    const dinosaurArt = document.getElementById('dinosaur-art');
    dinosaurArt.replaceChildren();
    if (typeof card.image_url === 'string' && card.image_url.trim()) {
        const image = document.createElement('img');
        image.src = `res/${encodeURIComponent(card.image_url.trim())}`;
        image.alt = nombre;
        image.addEventListener('error', () => {
            dinosaurArt.textContent = '◇';
        }, { once: true });
        dinosaurArt.append(image);
    } else {
        dinosaurArt.textContent = '◇';
    }

    document.getElementById('viewer-order').textContent = `DINO FILE · ${cardId}`;
    document.getElementById('viewer-index').textContent = `${String(index + 1).padStart(2, '0')} / ${String(total).padStart(2, '0')}`;
    document.getElementById('viewer-family').textContent = card.especie || 'ESPECIE SIN ESPECIFICAR';
    document.getElementById('viewer-name').textContent = nombre;
    document.getElementById('viewer-period').textContent = card.periodo || '—';
    document.getElementById('stats-species').textContent = card.especie || nombre;
    document.getElementById('stat-height').textContent = card.altura ?? '—';
    document.getElementById('stat-length').textContent = card.largo ?? '—';
    document.getElementById('stat-weight').textContent = card.peso ?? '—';
    document.getElementById('stat-period').textContent = card.periodo || '—';
    document.getElementById('stat-hp').textContent = card.hp ?? '—';
    document.getElementById('stat-vigor').textContent = card.vigor ?? '—';
    document.getElementById('stat-attack').textContent = card.ataque ?? '—';
    document.getElementById('stat-defense').textContent = card.defensa ?? '—';
    document.getElementById('stat-agility').textContent = card.agilidad ?? '—';
    document.getElementById('obtain-feedback').textContent = '';
}

function renderCollection(collection) {
    const cards = Array.isArray(collection)
        ? collection.filter((card) => card && Number.isSafeInteger(Number(card.id)) && Number(card.id) > 0)
            .map((card) => ({ ...card, id: Number(card.id) }))
        : [];

    speciesItems.forEach((item) => item.remove());
    speciesItems = [];
    collectionCount.textContent = String(cards.length).padStart(2, '0');
    emptySpecies.textContent = cards.length ? 'No hay cartas que coincidan.' : 'No hay cartas en tu colección.';
    emptySpecies.hidden = cards.length > 0;

    cards.forEach((card, index) => {
        const cardId = card.id;
        const button = document.createElement('button');
        button.className = 'species-item';
        button.type = 'button';
        button.dataset.cardId = String(cardId);

        const icon = document.createElement('span');
        icon.className = 'species-icon';
        icon.textContent = '◇';

        const name = document.createElement('span');
        name.className = 'species-name';
        name.append(card.nombre || `Carta #${cardId}`);

        const identifier = document.createElement('small');
        identifier.textContent = `${card.especie || 'Especie sin especificar'} · #${cardId}`;
        name.append(identifier);

        const arrow = document.createElement('span');
        arrow.className = 'species-arrow';
        arrow.textContent = '›';

        button.append(icon, name, arrow);
        button.addEventListener('click', () => selectCard(button, card, index, cards.length));
        speciesList.insertBefore(button, emptySpecies);
        speciesItems.push(button);
    });

    if (speciesItems.length) selectCard(speciesItems[0], cards[0], 0, speciesItems.length);
    else {
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
}

function mostrarAreaTrading()
{
    fetch()
}

renderCollection(JSON.parse(collectionView.dataset.collection || '[]'));