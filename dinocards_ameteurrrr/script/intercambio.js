document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('.intercambio-pagina');
    if (!root) return;
    const csrf = root.dataset.csrf || '';
    const select = document.getElementById('usuario-destino');
    const btnLoad = document.getElementById('cargar-cartas');
    const btnTrade = document.getElementById('realizar-intercambio');
    const ownBox = document.getElementById('mis-cartas');
    const otherBox = document.getElementById('sus-cartas');
    const ownCount = document.getElementById('contador-mias');
    const otherCount = document.getElementById('contador-suyas');
    const summary = document.getElementById('resumen-intercambio');
    const message = document.getElementById('mensaje-intercambio');
    let ownCards = [], otherCards = [], selectedOwn = new Set(), selectedOther = new Set();

    const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const say = (text, error=false) => { message.textContent = text; message.className = 'intercambio-mensaje' + (error ? ' error' : ' ok'); };
    async function request(url, options={}) {
        const response = await fetch(url, options);
        const data = await response.json();
        if (!response.ok && !data.mensaje) throw new Error('No se pudo completar la petición.');
        return data;
    }
    function renderCards(cards, box, selected, side) {
        if (!cards.length) { box.innerHTML = '<p class="intercambio-vacio">Este usuario todavía no tiene cartas.</p>'; return; }
        box.innerHTML = cards.map(card => {
            const id = Number(card.coleccion_id);
            const checked = selected.has(id) ? ' checked' : '';
            const image = card.image_url ? `<img src="${esc(card.image_url)}" alt="${esc(card.nombre)}" loading="lazy" onerror="this.style.display='none'">` : '<i class="bx bx-dna"></i>';
            return `<label class="intercambio-carta"><input type="checkbox" data-side="${side}" value="${id}"${checked}><span class="intercambio-imagen">${image}</span><strong>${esc(card.nombre)}</strong><small>${esc(card.especie)}</small><span class="intercambio-stats">HP ${esc(card.hp)} · ATQ ${esc(card.ataque)}</span></label>`;
        }).join('');
        box.querySelectorAll('input[type=checkbox]').forEach(input => input.addEventListener('change', () => {
            const target = input.dataset.side === 'own' ? selectedOwn : selectedOther;
            const id = Number(input.value);
            if (input.checked) target.add(id); else target.delete(id);
            updateSummary();
        }));
    }
    function updateSummary() {
        ownCount.textContent = `${selectedOwn.size} seleccionadas`;
        otherCount.textContent = `${selectedOther.size} seleccionadas`;
        const equal = selectedOwn.size > 0 && selectedOwn.size === selectedOther.size;
        summary.textContent = equal ? `Intercambio de ${selectedOwn.size} carta(s) por ${selectedOther.size} carta(s).` : 'Selecciona el mismo número de cartas en ambos lados.';
        btnTrade.disabled = !equal || !select.value;
    }
    async function loadUsers() {
        try {
            const data = await request('intercambio.php?ajax=usuarios');
            select.innerHTML = '<option value="">Elige un coleccionista...</option>' + data.usuarios.map(u => `<option value="${Number(u.id)}">${esc(u.username)}</option>`).join('');
            if (!data.usuarios.length) select.innerHTML = '<option value="">Todavía no hay otros usuarios registrados</option>';
        } catch (e) { select.innerHTML = '<option value="">No se pudieron cargar los usuarios</option>'; say(e.message, true); }
    }
    async function loadCards() {
        if (!select.value) { say('Selecciona un usuario primero.', true); return; }
        btnLoad.disabled = true; say('Cargando colecciones...');
        try {
            const data = await request(`intercambio.php?ajax=cartas&usuario=${encodeURIComponent(select.value)}`);
            if (!data.ok) throw new Error(data.mensaje || 'No se pudieron cargar las cartas.');
            ownCards = data.misCartas; otherCards = data.susCartas;
            selectedOwn.clear(); selectedOther.clear();
            document.getElementById('titulo-cartas-otro').textContent = `Cartas de ${data.otroUsuario}`;
            renderCards(ownCards, ownBox, selectedOwn, 'own');
            renderCards(otherCards, otherBox, selectedOther, 'other');
            updateSummary(); say('Colecciones cargadas. Selecciona las cartas que quieres intercambiar.');
        } catch (e) { say(e.message, true); }
        finally { btnLoad.disabled = false; }
    }
    btnLoad.addEventListener('click', loadCards);
    select.addEventListener('change', () => {
        ownCards = []; otherCards = []; selectedOwn.clear(); selectedOther.clear();
        ownBox.innerHTML = '<p class="intercambio-vacio">Pulsa «Ver colecciones» para cargar las cartas.</p>';
        otherBox.innerHTML = '<p class="intercambio-vacio">Las cartas del otro coleccionista aparecerán aquí.</p>';
        updateSummary(); say('');
    });
    btnTrade.addEventListener('click', async () => {
        if (btnTrade.disabled) return;
        if (!confirm('¿Confirmas el intercambio? Esta acción cambiará la propiedad de las cartas.')) return;
        btnTrade.disabled = true; say('Realizando intercambio...');
        try {
            const data = await request('intercambio.php?ajax=intercambiar', {
                method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-Token':csrf},
                body: JSON.stringify({usuario:Number(select.value),mis_cartas:[...selectedOwn],sus_cartas:[...selectedOther]})
            });
            if (!data.ok) throw new Error(data.mensaje || 'No se pudo realizar el intercambio.');
            selectedOwn.clear(); selectedOther.clear(); say(data.mensaje);
            await loadCards();
        } catch (e) { say(e.message, true); updateSummary(); }
    });
    loadUsers();
});
