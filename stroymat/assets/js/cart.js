document.addEventListener('DOMContentLoaded', function() {
    // Элементы корзины
    const cartIcon = document.getElementById('cart-icon');
    const cartModal = document.getElementById('cart-modal');
    const closeBtn = document.querySelector('.cart-close');
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total-price');
    const cartCounter = document.getElementById('cart-count');
    
    // Инициализация корзины
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    // Открытие/закрытие корзины
    cartIcon.addEventListener('click', () => {
        cartModal.style.display = 'block';
        renderCart();
    });
    
    closeBtn.addEventListener('click', () => {
        cartModal.style.display = 'none';
    });
    
    // Добавление в корзину
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            const product = this.closest('[data-id]');
            const id = product.dataset.id;
            const name = product.querySelector('.product-name').textContent;
            const price = parseFloat(product.querySelector('.product-price').textContent);
            const image = product.querySelector('img').src;
            
            addToCart({ id, name, price, image });
        });
    });
    
    // Функции
    function addToCart(item) {
        const existing = cart.find(i => i.id === item.id);
        if (existing) {
            existing.quantity++;
        } else {
            cart.push({...item, quantity: 1});
        }
        updateCart();
    }
    
    function renderCart() {
        cartItems.innerHTML = '';
        cart.forEach(item => {
            const itemEl = document.createElement('div');
            itemEl.className = 'cart-item';
            itemEl.innerHTML = `
                <img src="${item.image}" width="50">
                <span>${item.name}</span>
                <span>${item.price} руб. × ${item.quantity}</span>
                <button class="remove-item" data-id="${item.id}">×</button>
            `;
            cartItems.appendChild(itemEl);
        });
        
        // Общая сумма
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        cartTotal.textContent = total.toFixed(2);
    }
    
    function updateCart() {
        localStorage.setItem('cart', JSON.stringify(cart));
        cartCounter.textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
        renderCart();
    }
    
    // Удаление товара
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            const id = e.target.dataset.id;
            cart = cart.filter(item => item.id !== id);
            updateCart();
        }
    });
});