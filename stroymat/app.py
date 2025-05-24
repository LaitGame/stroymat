from flask import Flask, render_template, request, redirect, url_for, session, jsonify
import os

app = Flask(__name__)
app.secret_key = os.urandom(24)  # Секретный ключ для сессий

# База данных товаров (временная, можно заменить на реальную БД)
products = {
    1: {"name": "Кирпич красный", "price": 25, "image": "kirpich.jpg", "description": "Красный строительный кирпич, 250x120x65 мм"},
    2: {"name": "Цемент", "price": 300, "image": "cement.jpg", "description": "Цемент М500, мешок 50 кг"},
    3: {"name": "Доска обрезная", "price": 150, "image": "doska.jpg", "description": "Доска хвойных пород, 25x100x6000 мм"},
    4: {"name": "Шифер", "price": 200, "image": "shifer.jpg", "description": "Волновой шифер, 8-волновой, 1750x1130x5.8 мм"},
    5: {"name": "Песок", "price": 50, "image": "pesok.jpg", "description": "Речной песок, мешок 40 кг"},
    6: {"name": "Гвозди", "price": 10, "image": "gvozdi.jpg", "description": "Гвозди строительные, 3.0x80 мм, 1 кг"}
}

@app.route('/')
def index():
    return render_template('index.html', products=products)

@app.route('/product/<int:product_id>')
def product_detail(product_id):
    product = products.get(product_id)
    if not product:
        return redirect(url_for('index'))
    return render_template('product.html', product=product)

@app.route('/add_to_cart', methods=['POST'])
def add_to_cart():
    if request.method == 'POST':
        product_id = int(request.form.get('product_id'))
        quantity = int(request.form.get('quantity', 1))
        
        if 'cart' not in session:
            session['cart'] = {}
        
        cart = session['cart']
        cart[product_id] = cart.get(product_id, 0) + quantity
        session['cart'] = cart
        
        return jsonify({
            'success': True,
            'cart_total': sum(cart.values())
        })

@app.route('/cart')
def view_cart():
    cart = session.get('cart', {})
    cart_items = []
    total = 0
    
    for product_id, quantity in cart.items():
        product = products.get(product_id)
        if product:
            item_total = product['price'] * quantity
            cart_items.append({
                'id': product_id,
                'name': product['name'],
                'price': product['price'],
                'quantity': quantity,
                'total': item_total,
                'image': product['image']
            })
            total += item_total
    
    return render_template('cart.html', cart_items=cart_items, total=total)

@app.route('/update_cart', methods=['POST'])
def update_cart():
    product_id = int(request.form.get('product_id'))
    quantity = int(request.form.get('quantity', 1))
    
    if quantity <= 0:
        if 'cart' in session and product_id in session['cart']:
            del session['cart'][product_id]
    else:
        if 'cart' in session:
            session['cart'][product_id] = quantity
    
    session.modified = True
    return redirect(url_for('view_cart'))

@app.route('/remove_from_cart/<int:product_id>')
def remove_from_cart(product_id):
    if 'cart' in session and product_id in session['cart']:
        del session['cart'][product_id]
        session.modified = True
    return redirect(url_for('view_cart'))

@app.route('/cart_count')
def cart_count():
    count = sum(session.get('cart', {}).values()) if 'cart' in session else 0
    return jsonify({'count': count})

if __name__ == '__main__':
    app.run(debug=True)