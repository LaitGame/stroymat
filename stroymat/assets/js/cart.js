document.addEventListener('DOMContentLoaded', function() {
    // Инициализация корзины
    updateCartCount();
    
    // Обработчики для всех кнопок "Добавить в корзину"
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const quantity = this.dataset.quantity || 1;
            addToCart(productId, quantity);
        });
    });
    
    // Обработчики для кнопок изменения количества в корзине
    document.querySelectorAll('.cart-quantity-update').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const newQuantity = this.value;
            updateCartItem(productId, newQuantity);
        });
    });
    
    // Обработчики для кнопок удаления из корзины
    document.querySelectorAll('.remove-from-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            removeFromCart(productId);
        });
    });
});

/**
 * Добавляет товар в корзину
 */
function addToCart(productId, quantity = 1) {
    if (!productId) {
        showAlert('Ошибка: не указан ID товара', 'error');
        return;
    }

    sendCartRequest('add', {
        product_id: productId,
        quantity: quantity
    }).then(data => {
        if (data.success) {
            showAlert('Товар добавлен в корзину!');
            updateCartUI(data);
        } else {
            showAlert(data.message || 'Ошибка при добавлении в корзину', 'error');
        }
    }).catch(error => {
        console.error('Error:', error);
        showAlert('Произошла ошибка при добавлении в корзину', 'error');
    });
}

/**
 * Обновляет количество товара в корзине
 */
function updateCartItem(productId, quantity) {
    if (!productId || quantity <= 0) {
        showAlert('Некорректное количество', 'error');
        return;
    }

    sendCartRequest('update', {
        product_id: productId,
        quantity: quantity
    }).then(data => {
        if (data.success) {
            updateCartUI(data);
        } else {
            showAlert(data.message || 'Ошибка при обновлении корзины', 'error');
            // Восстанавливаем предыдущее значение
            location.reload();
        }
    });
}

/**
 * Удаляет товар из корзины
 */
function removeFromCart(productId) {
    if (!productId) return;

    if (!confirm('Вы уверены, что хотите удалить товар из корзины?')) {
        return;
    }

    sendCartRequest('remove', {
        product_id: productId
    }).then(data => {
        if (data.success) {
            showAlert('Товар удален из корзины');
            updateCartUI(data);
            // Удаляем строку из таблицы
            document.querySelector(`tr[data-product-id="${productId}"]`)?.remove();
            // Пересчитываем итого
            calculateCartTotal();
        }
    });
}

/**
 * Отправляет запрос к серверу для работы с корзиной
 */
function sendCartRequest(action, data) {
    return fetch('/includes/cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: action,
            ...data
        })
    }).then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    });
}

/**
 * Обновляет UI корзины
 */
function updateCartUI(data) {
    // Обновляем счетчик в шапке
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = data.cart_count || 0;
    }
    
    // Обновляем общую сумму, если есть данные
    if (data.total !== undefined) {
        updateCartTotal(data.total);
    }
}

/**
 * Обновляет счетчик товаров в корзине
 */
function updateCartCount() {
    sendCartRequest('get').then(data => {
        if (data.success) {
            updateCartUI(data);
        }
    }).catch(error => {
        console.error('Error updating cart count:', error);
    });
}

/**
 * Обновляет отображение общей суммы
 */
function updateCartTotal(total) {
    const totalElement = document.getElementById('cart-total');
    if (totalElement) {
        totalElement.textContent = total.toFixed(2) + ' руб.';
    }
}

/**
 * Пересчитывает общую сумму корзины
 */
function calculateCartTotal() {
    let total = 0;
    document.querySelectorAll('.cart-item').forEach(item => {
        const price = parseFloat(item.dataset.price);
        const quantity = parseInt(item.querySelector('.cart-quantity').value);
        total += price * quantity;
    });
    updateCartTotal(total);
}

/**
 * Показывает уведомление
 */
function showAlert(message, type = 'success') {
    // Можно заменить на любую систему уведомлений
    alert(message);
}

/**
 * Инициализирует обработчики для динамически добавленных элементов
 */
function initDynamicCartElements() {
    // Для динамически загруженного контента корзины
    document.querySelectorAll('.dynamic-cart-element').forEach(element => {
    });
}