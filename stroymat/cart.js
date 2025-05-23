document.addEventListener('DOMContentLoaded', function() {
    // Элементы DOM
    const cartIcon = document.getElementById('cart-icon');
    const cartModal = document.getElementById('cart-modal');
    const closeModal = document.querySelector('.cart-close');
    const cartItemsContainer = document.getElementById('cart-items');
    const cartTotalPrice = document.getElementById('cart-total-price');
    const cartCount = document.getElementById('cart-count');
    const checkoutBtn = document.getElementById('checkout-btn');
    
    // Инициализация корзины
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    // Открытие/закрытие модального окна
    if(cartIcon) cartIcon.addEventListener('click', openCartModal);
    if(closeModal) closeModal.addEventListener('click', closeCartModal);
    window.addEventListener('click', outsideClick);
    
    // Обновление корзины при загрузке страницы
    updateCart();
    
    // Добавление обработчиков для кнопок "Добавить в корзину"
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', addToCart);
    });
    
    // Обработчик для кнопки оформления заказа
    if(checkoutBtn) checkoutBtn.addEventListener('click', checkout);
    
    // Функции
    
    function openCartModal() {
        cartModal.style.display = 'block';
        renderCartItems();
    }
    
    function closeCartModal() {
        cartModal.style.display = 'none';
    }
    
    function outsideClick(e) {
        if (e.target === cartModal) {
            closeCartModal();
        }
    }
    
    function addToCart(e) {
        const button = e.target;
        const productCard = button.closest('.card');
        
        const product = {
            id: productCard.dataset.id || Math.random().toString(36).substr(2, 9),
            name: productCard.querySelector('.card-title').textContent,
            price: parseFloat(productCard.querySelector('.price').textContent.replace(/[^\d.]/g, '')),
            image: productCard.querySelector('img').src,
            quantity: 1
        };
        
        // Проверяем, есть ли товар уже в корзине
        const existingItem = cart.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push(product);
        }
        
        updateCart();
        showAddedToCartMessage(product.name);
    }
    
    function updateCart() {
        // Сохраняем корзину в localStorage
        localStorage.setItem('cart', JSON.stringify(cart));
        
        // Обновляем счетчик товаров
        const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
        if(cartCount) cartCount.textContent = totalItems;
        
        // Обновляем общую сумму
        const totalPrice = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        if(cartTotalPrice) cartTotalPrice.textContent = totalPrice.toFixed(2);
        
        // Если корзина открыта, обновляем список товаров
        if (cartModal && cartModal.style.display === 'block') {
            renderCartItems();
        }
    }
    
    function renderCartItems() {
        if(!cartItemsContainer) return;
        
        cartItemsContainer.innerHTML = '';
        
        if (cart.length === 0) {
            cartItemsContainer.innerHTML = '<p>Ваша корзина пуста</p>';
            return;
        }
        
        cart.forEach(item => {
            const cartItemElement = document.createElement('div');
            cartItemElement.className = 'cart-item';
            cartItemElement.innerHTML = `
                <img src="${item.image}" alt="${item.name}" class="cart-item-img">
                <div class="cart-item-info">
                    <h5>${item.name}</h5>
                    <div class="cart-item-controls">
                        <button class="btn btn-sm btn-outline-secondary decrease-quantity">-</button>
                        <input type="number" value="${item.quantity}" min="1" class="form-control item-quantity">
                        <button class="btn btn-sm btn-outline-secondary increase-quantity">+</button>
                        <button class="btn btn-sm btn-outline-danger remove-item">×</button>
                    </div>
                </div>
                <div class="cart-item-price">${(item.price * item.quantity).toFixed(2)} руб.</div>
            `;
            
            cartItemsContainer.appendChild(cartItemElement);
            
            // Добавляем обработчики
            cartItemElement.querySelector('.decrease-quantity').addEventListener('click', () => changeQuantity(item.id, -1));
            cartItemElement.querySelector('.increase-quantity').addEventListener('click', () => changeQuantity(item.id, 1));
            cartItemElement.querySelector('.remove-item').addEventListener('click', () => removeItem(item.id));
            cartItemElement.querySelector('.item-quantity').addEventListener('change', (e) => {
                const newQuantity = parseInt(e.target.value);
                if (newQuantity > 0) {
                    updateItemQuantity(item.id, newQuantity);
                }
            });
        });
    }
    
    function changeQuantity(itemId, change) {
        const item = cart.find(item => item.id === itemId);
        if (item) {
            const newQuantity = item.quantity + change;
            if (newQuantity > 0) {
                item.quantity = newQuantity;
            } else {
                cart = cart.filter(item => item.id !== itemId);
            }
            updateCart();
        }
    }
    
    function updateItemQuantity(itemId, newQuantity) {
        const item = cart.find(item => item.id === itemId);
        if (item) {
            item.quantity = newQuantity;
            updateCart();
        }
    }
    
    function removeItem(itemId) {
        cart = cart.filter(item => item.id !== itemId);
        updateCart();
    }
    
    function showAddedToCartMessage(productName) {
        const message = document.createElement('div');
        message.className = 'alert alert-success added-to-cart-message';
        message.innerHTML = `<i class="bi bi-check-circle"></i> ${productName} добавлен в корзину!`;
        document.body.appendChild(message);
        
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 500);
        }, 3000);
    }
    
    function checkout() {
        if (cart.length === 0) {
            alert('Ваша корзина пуста!');
            return;
        }
        
        // Отправка данных на сервер
        fetch('/order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cart: cart,
                total: cart.reduce((total, item) => total + (item.price * item.quantity), 0)
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Заказ успешно оформлен! Номер заказа: ' + data.order_id);
                cart = [];
                updateCart();
                closeCartModal();
            } else {
                alert('Ошибка: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при оформлении заказа');
        });
    }
});