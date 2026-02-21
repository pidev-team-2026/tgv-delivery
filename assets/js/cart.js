/**
 * ═══════════════════════════════════════════════
 *  CART.JS — TGV DELIVERY
 *  Panier + Code Promo + Estimation Livraison
 * ═══════════════════════════════════════════════
 */

const CART_KEY  = 'tgv_cart';
const LIVRAISON_DEFAULT = 7.000;

// ── État global promo ─────────────────────────
let promoActif = {
    code: null,
    remise: 0,
    fraisLivraison: LIVRAISON_DEFAULT,
    label: null
};

// ── Données panier ────────────────────────────
const cart = {

    getItems() {
        try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; }
        catch { return []; }
    },

    saveItems(items) {
        localStorage.setItem(CART_KEY, JSON.stringify(items));
        cartUI.refresh();
    },

    addItem(id, name, price, originalPrice, image, stock) {
        const items = this.getItems();
        const idx   = items.findIndex(i => i.id === id);
        if (idx >= 0) {
            if (items[idx].qty < (stock || 99)) items[idx].qty++;
        } else {
            items.push({
                id,
                name,
                price: parseFloat(price),
                originalPrice: parseFloat(originalPrice || price),
                image: image || null,
                stock: stock || 99,
                qty: 1
            });
        }
        this.saveItems(items);
    },

    removeItem(id) {
        this.saveItems(this.getItems().filter(i => i.id !== id));
    },

    updateQty(id, qty) {
        if (qty <= 0) { this.removeItem(id); return; }
        const items = this.getItems();
        const idx   = items.findIndex(i => i.id === id);
        if (idx >= 0) {
            items[idx].qty = Math.min(qty, items[idx].stock || 99);
            this.saveItems(items);
        }
    },

    clearCart() {
        this.saveItems([]);
        promoActif = { code: null, remise: 0, fraisLivraison: LIVRAISON_DEFAULT, label: null };
    },

    getTotals() {
        const items = this.getItems();
        let subtotal = 0;
        items.forEach(i => { subtotal += i.price * i.qty; });
        const remise   = promoActif.remise;
        const livraison = promoActif.fraisLivraison;
        return {
            subtotal,
            remise,
            livraison,
            total: Math.max(0, subtotal - remise) + livraison
        };
    },

    getCount() {
        return this.getItems().reduce((s, i) => s + i.qty, 0);
    }
};

// ── Formatage TND ─────────────────────────────
function fmtTND(amount) {
    return parseFloat(amount).toFixed(3).replace('.', ',') + ' TND';
}

// ── UI panneau panier ─────────────────────────
const cartUI = {

    open() {
        document.getElementById('cartPanel').classList.add('open');
        document.getElementById('cartOverlay').classList.add('open');
        document.body.style.overflow = 'hidden';
        this.showView('cart');
        this.refresh();
    },

    close() {
        document.getElementById('cartPanel').classList.remove('open');
        document.getElementById('cartOverlay').classList.remove('open');
        document.body.style.overflow = '';
    },

    showView(view) {
        document.getElementById('cartViewCart').style.display     = view === 'cart'     ? 'flex' : 'none';
        document.getElementById('cartViewCheckout').style.display = view === 'checkout' ? 'flex' : 'none';
    },

    refresh() {
        const count = cart.getCount();

        // Badge navbar
        const badge = document.getElementById('cartNavBadge');
        if (badge) { badge.textContent = count; badge.style.display = count > 0 ? 'flex' : 'none'; }

        // Count header panneau
        const hCount = document.getElementById('cartPanelCount');
        if (hCount) hCount.textContent = count;

        this.renderItems(cart.getItems());
        this.renderTotals();
    },

    renderItems(items) {
        const list  = document.getElementById('cartItemsList');
        const empty = document.getElementById('cartEmpty');
        const footer = document.getElementById('cartPanelFooter');
        if (!list) return;

        list.innerHTML = '';

        if (items.length === 0) {
            if (empty) empty.style.display = 'flex';
            list.style.display = 'none';
            if (footer) footer.style.display = 'none';
            return;
        }

        if (empty) empty.style.display = 'none';
        list.style.display = 'block';
        if (footer) footer.style.display = 'block';

        items.forEach(item => {
            const div = document.createElement('div');
            div.className = 'cart-item';
            div.innerHTML = `
                <div class="cart-item-thumb">
                    ${item.image ? `<img src="${item.image}" alt="${item.name}">` : '🛍️'}
                </div>
                <div class="cart-item-details">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-unit-price">${fmtTND(item.price)}</div>
                    <div class="qty-controls">
                        <button class="qty-btn" onclick="cart.updateQty(${item.id}, ${item.qty - 1})">−</button>
                        <span class="qty-num">${item.qty}</span>
                        <button class="qty-btn" onclick="cart.updateQty(${item.id}, ${item.qty + 1})">+</button>
                    </div>
                </div>
                <div class="cart-item-right">
                    <span class="cart-item-total">${fmtTND(item.price * item.qty)}</span>
                    <button class="cart-item-delete" onclick="cart.removeItem(${item.id})">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            `;
            list.appendChild(div);
        });
    },

    renderTotals() {
        const t  = cart.getTotals();
        const el = id => document.getElementById(id);

        if (el('cartSubtotal'))  el('cartSubtotal').textContent  = fmtTND(t.subtotal);
        if (el('cartLivraison')) el('cartLivraison').textContent = fmtTND(t.livraison);
        if (el('cartTotal'))     el('cartTotal').textContent     = fmtTND(t.total);

        // Ligne remise dans le résumé panier
        const remiseLine = el('cartRemiseLine');
        if (remiseLine) {
            remiseLine.style.display = t.remise > 0 ? 'flex' : 'none';
            const remiseVal = el('cartRemiseVal');
            if (remiseVal) remiseVal.textContent = '−' + fmtTND(t.remise);
        }
    },

    goToCheckout() {
        this.showView('checkout');
        this.syncCheckoutTotals();

        // Demander estimation si gouvernorat déjà saisi
        const gov = document.getElementById('govInput');
        if (gov && gov.value) fetchEstimation(gov.value);
    },

    syncCheckoutTotals() {
        const t  = cart.getTotals();
        const el = id => document.getElementById(id);
        if (el('ckSubtotal'))  el('ckSubtotal').textContent  = fmtTND(t.subtotal);
        if (el('ckRemise'))    el('ckRemise').textContent    = t.remise > 0 ? '−' + fmtTND(t.remise) : '-';
        if (el('ckLivraison')) el('ckLivraison').textContent = fmtTND(t.livraison);
        if (el('ckTotal'))     el('ckTotal').textContent     = fmtTND(t.total);

        const remiseLine = el('ckRemiseLine');
        if (remiseLine) remiseLine.style.display = t.remise > 0 ? 'flex' : 'none';
    },

    backToCart() {
        this.showView('cart');
    }
};

// ── CODE PROMO ────────────────────────────────
function appliquerPromo() {
    const input = document.getElementById('promoInput');
    const code  = input ? input.value.trim().toUpperCase() : '';
    const btn   = document.getElementById('promoBtn');
    const msg   = document.getElementById('promoMsg');

    if (!code) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';

    const t = cart.getTotals();

    fetch('/commande/api/promo/verifier', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code, total: t.subtotal })
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = 'Appliquer';

        if (res.success) {
            promoActif.code           = res.code;
            promoActif.remise         = res.remise;
            promoActif.fraisLivraison = res.fraisLivraison;
            promoActif.label          = res.label;

            msg.className = 'promo-msg success';
            msg.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${res.label} appliqué !`;

            cartUI.renderTotals();
            cartUI.syncCheckoutTotals();
            showToast('Code promo appliqué 🎉');
        } else {
            msg.className = 'promo-msg error';
            msg.innerHTML = `<i class="bi bi-x-circle-fill"></i> ${res.message}`;
        }
        msg.style.display = 'flex';
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = 'Appliquer';
        msg.className = 'promo-msg error';
        msg.innerHTML = '<i class="bi bi-x-circle-fill"></i> Erreur de connexion';
        msg.style.display = 'flex';
    });
}

// ── ESTIMATION LIVRAISON ──────────────────────
function fetchEstimation(gouvernorat) {
    if (!gouvernorat) return;

    fetch('/commande/api/livraison/estimation', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ gouvernorat: gouvernorat.toLowerCase() })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const box = document.getElementById('estimationLivraison');
const txt = document.getElementById('estimationText');
if (box && txt) {
    txt.textContent = res.label;
    box.style.display = 'flex';
}
        }
    })
    .catch(() => {});
}

// ── Gouvernorats autocomplete ─────────────────
const GOUVERNORATS = [
    'Ariana','Béja','Ben Arous','Bizerte','Gabès','Gafsa','Jendouba',
    'Kairouan','Kasserine','Kébili','Kef','Mahdia','Manouba','Médenine',
    'Monastir','Nabeul','Sfax','Sidi Bouzid','Siliana','Sousse',
    'Tataouine','Tozeur','Tunis','Zaghouan'
];

function initGouvernoratInput() {
    const input = document.getElementById('govInput');
    const list  = document.getElementById('govList');
    if (!input || !list) return;

    function render(q) {
        const filtered = q
            ? GOUVERNORATS.filter(g => g.toLowerCase().includes(q.toLowerCase()))
            : GOUVERNORATS;
        list.innerHTML = filtered.map(g =>
            `<div class="gov-option" onclick="selectGov('${g}')">${g}</div>`
        ).join('');
        list.classList.toggle('open', filtered.length > 0);
    }

    input.addEventListener('focus', () => render(input.value));
    input.addEventListener('input', () => render(input.value));
    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !list.contains(e.target))
            list.classList.remove('open');
    });
}

function selectGov(name) {
    const input = document.getElementById('govInput');
    if (input) input.value = name;
    const list = document.getElementById('govList');
    if (list) list.classList.remove('open');

    // Déclencher estimation dès qu'un gouvernorat est sélectionné
    fetchEstimation(name);
    cartUI.syncCheckoutTotals();
}

// ── Payment selection ─────────────────────────
function selectPayment(el) {
    document.querySelectorAll('.payment-opt').forEach(o => {
        o.classList.remove('selected');
        const chk = o.querySelector('.payment-opt-check');
        if (chk) chk.innerHTML = '';
    });
    el.classList.add('selected');
    const chk = el.querySelector('.payment-opt-check');
    if (chk) chk.innerHTML = '<i class="bi bi-check"></i>';
}

// ── Submit commande ───────────────────────────
function submitOrder(e) {
    e.preventDefault();
    const form = document.getElementById('checkoutForm');
    const data = Object.fromEntries(new FormData(form).entries());

    // Ajouter items + promo
    data.items          = cart.getItems();
    data.remise         = promoActif.remise;
    data.fraisLivraison = promoActif.fraisLivraison;
    data.codePromo      = promoActif.code;

    // Désactiver le bouton pendant l'envoi
    const btn = form.querySelector('.btn-valider');
    const origHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Enregistrement...';

    fetch('/commande/api/client/commande', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            cart.clearCart();
            cartUI.close();
            showToast(`✅ Commande ${res.reference} enregistrée !`);
            setTimeout(() => window.location.href = '/client/commandes', 2500);
        } else {
            btn.disabled = false;
            btn.innerHTML = origHTML;
            showToast('❌ Erreur : ' + (res.message || 'Réessayez.'));
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = origHTML;
        showToast('❌ Erreur de connexion.');
    });
}

// ── Toast ─────────────────────────────────────
function showToast(msg) {
    const t = document.getElementById('tgvToast');
    if (!t) return;
    document.getElementById('tgvToastMsg').textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3500);
}

// ── Exposer globalement ───────────────────────
window.cart            = cart;
window.cartUI          = cartUI;
window.showToast       = showToast;
window.selectGov       = selectGov;
window.selectPayment   = selectPayment;
window.submitOrder     = submitOrder;
window.appliquerPromo  = appliquerPromo;
window.fetchEstimation = fetchEstimation;

// ── Init ──────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    cartUI.refresh();
    initGouvernoratInput();

    const overlay = document.getElementById('cartOverlay');
    if (overlay) overlay.addEventListener('click', () => cartUI.close());

    const cartBtn = document.getElementById('cartIconBtn');
    if (cartBtn) cartBtn.addEventListener('click', () => cartUI.open());

    // Promo : Enter key
    const promoInput = document.getElementById('promoInput');
    if (promoInput) {
        promoInput.addEventListener('keypress', e => {
            if (e.key === 'Enter') { e.preventDefault(); appliquerPromo(); }
        });
    }
});
