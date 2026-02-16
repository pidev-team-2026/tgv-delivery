class ShoppingCart {
    constructor() {
        this.items = this.loadCart();
        this.updateCartUI();
    }

    loadCart() {
        const saved = localStorage.getItem('tgv_cart');
        return saved ? JSON.parse(saved) : [];
    }

    saveCart() {
        localStorage.setItem('tgv_cart', JSON.stringify(this.items));
        this.updateCartUI();
    }

    addItem(id, name, price, image, stock) {
        const item = this.items.find(i => i.id === id);
        if (item) {
            item.quantity++;
        } else {
            this.items.push({ id, name, price: parseFloat(price), quantity: 1, image, maxStock: stock });
        }
        this.saveCart();
        this.showNotification('✓ ' + name + ' ajouté');
    }

    updateQuantity(id, qty) {
        const item = this.items.find(i => i.id === id);
        if (item) {
            if (qty <= 0) {
                this.removeItem(id);
            } else {
                item.quantity = qty;
                this.saveCart();
            }
        }
    }

    removeItem(id) {
        this.items = this.items.filter(i => i.id !== id);
        this.saveCart();
    }

    clearCart() {
        if (confirm('Vider le panier ?')) {
            this.items = [];
            this.saveCart();
        }
    }

    getTotal() {
        return this.items.reduce((sum, i) => sum + (i.price * i.quantity), 0);
    }

    getTotalItems() {
        return this.items.reduce((sum, i) => sum + i.quantity, 0);
    }

    updateCartUI() {
        const badge = document.querySelector('.cart-badge');
        if (badge) {
            const total = this.getTotalItems();
            badge.textContent = total;
            badge.style.display = total > 0 ? 'flex' : 'none';
        }
        this.renderCartModal();
    }

    renderCartModal() {
        const container = document.getElementById('cartItemsList');
        const empty = document.getElementById('emptyCartMessage');
        const checkout = document.getElementById('checkoutSection');
        const total = document.getElementById('cartTotal');

        if (!container) return;

        if (this.items.length === 0) {
            container.innerHTML = '';
            if (empty) empty.style.display = 'block';
            if (checkout) checkout.style.display = 'none';
            return;
        }

        if (empty) empty.style.display = 'none';
        if (checkout) checkout.style.display = 'block';

        container.innerHTML = this.items.map(item => `
            <div class="cart-item">
                <div class="cart-item-image">
                    ${item.image ? `<img src="${item.image}" alt="${item.name}">` : '<div class="no-image"><i class="bi bi-image"></i></div>'}
                </div>
                <div class="cart-item-details">
                    <h6>${item.name}</h6>
                    <p class="text-muted mb-1">${item.price.toFixed(2)}€</p>
                    <div class="quantity-control">
                        <button class="btn btn-sm btn-outline-secondary" onclick="cart.updateQuantity(${item.id}, ${item.quantity - 1})">
                            <i class="bi bi-dash"></i>
                        </button>
                        <input type="number" value="${item.quantity}" min="1" onchange="cart.updateQuantity(${item.id}, parseInt(this.value))" class="form-control form-control-sm">
                        <button class="btn btn-sm btn-outline-secondary" onclick="cart.updateQuantity(${item.id}, ${item.quantity + 1})">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="cart-item-price">
                    <strong>${(item.price * item.quantity).toFixed(2)}€</strong>
                    <button class="btn btn-sm btn-link text-danger" onclick="cart.removeItem(${item.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');

        if (total) total.textContent = this.getTotal().toFixed(2) + '€';
        this.updateOrderSummary();
    }

    updateOrderSummary() {
        const subtotal = this.getTotal();
        const delivery = document.querySelector('input[name="deliveryType"]:checked');
        const shipping = delivery && delivery.value === 'express' ? 9.99 : 4.99;
        const totalAmount = subtotal + shipping;

        const sub = document.getElementById('summarySubtotal');
        const ship = document.getElementById('summaryShipping');
        const tot = document.getElementById('summaryTotal');

        if (sub) sub.textContent = subtotal.toFixed(2) + '€';
        if (ship) ship.textContent = shipping.toFixed(2) + '€';
        if (tot) tot.textContent = totalAmount.toFixed(2) + '€';
    }

    showNotification(msg) {
        const n = document.createElement('div');
        n.className = 'alert alert-success cart-notification';
        n.textContent = msg;
        document.body.appendChild(n);
        setTimeout(() => n.classList.add('show'), 10);
        setTimeout(() => {
            n.classList.remove('show');
            setTimeout(() => n.remove(), 300);
        }, 2000);
    }
}

const cart = new ShoppingCart();

function openCartModal() {
    cart.renderCartModal();
    new bootstrap.Modal(document.getElementById('cartModal')).show();
}

function proceedToCheckout() {
    if (cart.items.length === 0) {
        alert('Panier vide');
        return;
    }
    document.getElementById('cartView').style.display = 'none';
    document.getElementById('checkoutView').style.display = 'block';
    document.getElementById('modalTitle').textContent = 'Finaliser ma commande';
    cart.updateOrderSummary();
}

function backToCart() {
    document.getElementById('cartView').style.display = 'block';
    document.getElementById('checkoutView').style.display = 'none';
    document.getElementById('modalTitle').textContent = 'Mon Panier';
}

function submitOrder(e) {
    e.preventDefault();
    alert('✅ Commande validée !');
    cart.clearCart();
    bootstrap.Modal.getInstance(document.getElementById('cartModal')).hide();
    backToCart();
    document.getElementById('checkoutForm').reset();
}

// ============================================
// FORMATAGE AUTOMATIQUE CARTE BANCAIRE
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const cardNumber = document.getElementById('cardNumber');
    const cardExpiry = document.getElementById('cardExpiry');
    const cardCvv = document.getElementById('cardCvv');
    
    // Formatage numéro de carte (XXXX XXXX XXXX XXXX)
    if (cardNumber) {
        cardNumber.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });
    }
    
    // Formatage date expiration (MM/AA)
    if (cardExpiry) {
        cardExpiry.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
    }
    
    // CVV : seulement chiffres
    if (cardCvv) {
        cardCvv.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
    }
});

// ============================================
// AFFICHAGE FORMULAIRES PAIEMENT (MISE À JOUR)
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const paymentRadios = document.querySelectorAll('input[name="paymentMethod"]');
    const cardForm = document.getElementById('cardForm');
    const googlePayForm = document.getElementById('googlePayForm');
    const qrCodeForm = document.getElementById('qrCodeForm');
    
    if (!paymentRadios.length) return;
    
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Cacher tous les formulaires
            if (cardForm) cardForm.style.display = 'none';
            if (googlePayForm) googlePayForm.style.display = 'none';
            if (qrCodeForm) qrCodeForm.style.display = 'none';
            
            // Afficher le formulaire correspondant
            if (this.value === 'card' && cardForm) {
                cardForm.style.display = 'block';
            } else if (this.value === 'googlepay' && googlePayForm) {
                googlePayForm.style.display = 'block';
            } else if (this.value === 'mobile' && qrCodeForm) {
                qrCodeForm.style.display = 'block';
            }
        });
    });
    
    // Afficher le formulaire carte par défaut
    if (cardForm) cardForm.style.display = 'block';
});
