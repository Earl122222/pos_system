<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminOrUserLogin();

// Fetch category for the dropdown
$categorys = $pdo->query("SELECT category_id, category_name FROM pos_category WHERE category_status = 'Active'")->fetchAll(PDO::FETCH_ASSOC);

$confData = getConfigData($pdo);

include('header.php');
?>

<!-- Add the custom CSS file -->
<link rel="stylesheet" href="asset/css/order-custom.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Categories Menu -->
            <div class="category-menu">
                <button type="button" class="category-btn active" onclick="load_category_product('all')">
                    <i class="fas fa-utensils"></i>
                    All Menu
                    <small>4 items</small>
                </button>
                <button type="button" class="category-btn" onclick="load_category_product('coffee')">
                    <i class="fas fa-coffee"></i>
                    Coffee
                    <small>2 items</small>
                </button>
                <button type="button" class="category-btn" onclick="load_category_product('tea')">
                    <i class="fas fa-mug-hot"></i>
                    Tea
                    <small>3 items</small>
                </button>
                <button type="button" class="category-btn" onclick="load_category_product('mocktail')">
                    <i class="fas fa-glass-martini-alt"></i>
                    Mocktail
                    <small>8 items</small>
                </button>
                <button type="button" class="category-btn" onclick="load_category_product('rice')">
                    <i class="fas fa-bowl-rice"></i>
                    Rice
                    <small>4 items</small>
                </button>
                <button type="button" class="category-btn" onclick="load_category_product('pasta')">
                    <i class="fas fa-wheat-awn"></i>
                    Pasta
                    <small>4 items</small>
                </button>
                <button type="button" class="category-btn" onclick="load_category_product('burger')">
                    <i class="fas fa-hamburger"></i>
                    Burger
                    <small>6 items</small>
                </button>
            </div>

            <!-- Menu Section Title -->
            <h5 class="mb-3" id="menu-title">All Menu</h5>
            <div class="row" id="dynamic_item">
                <!-- Items will be loaded here -->
            </div>
        </div>

        <div class="col-md-4">
            <div class="card order-section">
                <div class="order-header">
                    <h5 class="order-title">Invoice</h5>
                </div>
                
                <div class="service-types">
                    <div class="service-type active" onclick="setServiceType('dine-in')">
                        <i class="fas fa-utensils"></i>
                        <span>Dine in</span>
                    </div>
                    <div class="service-type" onclick="setServiceType('takeout')">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Takeout</span>
                    </div>
                    <div class="service-type" onclick="setServiceType('delivery')">
                        <i class="fas fa-motorcycle"></i>
                        <span>Delivery</span>
                    </div>
                </div>

                <div class="order-items" id="order_item_details">
                    <!-- Order items will be loaded here -->
                </div>

                <div class="payment-summary">
                    <div class="summary-row">
                        <span>Sub Total</span>
                        <span id="order_gross_total">0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (<?php echo floatval($confData['tax_rate']); ?>%)</span>
                        <span id="order_taxes">0.00</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Payment</span>
                        <span id="order_net_total">0.00</span>
                    </div>
                </div>

                <div class="payment-methods">
                    <div class="payment-method active">
                        <i class="fas fa-credit-card"></i>
                        <div>Credit Card</div>
                    </div>
                    <div class="payment-method">
                        <i class="fas fa-money-bill-wave"></i>
                        <div>Cash</div>
                    </div>
                    <div class="payment-method">
                        <i class="fas fa-qrcode"></i>
                        <div>E-Wallet</div>
                    </div>
                </div>

                <button type="button" class="btn-place-order" id="order_btn" onclick="createOrder()" disabled>
                    Place An Order
                </button>
            </div>
        </div>
    </div>
</div>

<script>

load_category_product();

function load_category_product(category_id = 'all')
{
    // Update menu title based on selected category
    const menuTitle = document.getElementById('menu-title');
    const categoryText = category_id === 'all' ? 'All Menu' : 
        category_id.charAt(0).toUpperCase() + category_id.slice(1) + ' Menu';
    menuTitle.textContent = categoryText;

    // Convert 'all' to 0 or empty string for the backend
    const apiCategoryId = category_id === 'all' ? '' : category_id;

    fetch('order_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            category_id: apiCategoryId,
            action: 'get_products'
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('API Response:', data); // Debug log
        let html = '';
        if(data.length > 0){
            for(let i = 0; i < data.length; i++){
                // Handle image path properly
                let imageUrl;
                if (data[i].product_image) {
                    // Use the full path from the database
                    imageUrl = data[i].product_image;
                    console.log('Image path from database:', imageUrl);
                } else {
                    imageUrl = 'asset/images/default-food.jpg';
                }

                const description = data[i].product_description || 'Delicious food with special sauce';
                const itemId = data[i].product_id;
                const itemName = data[i].product_name.replace(/'/g, "\\'");
                html += `
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="item-card">
                        <div class="item-image-container">
                            <img src="${imageUrl}" 
                                 alt="${itemName}" 
                                 class="item-image"
                                 onerror="this.onerror=null; this.src='asset/images/default-food.jpg';">
                        </div>
                        <div class="item-info">
                            <h3 class="item-name">${itemName}</h3>
                            <p class="item-description">${description}</p>
                            <div class="item-footer">
                                <div class="price-section">
                                    <span class="currency">₱</span>
                                    <span class="item-price">${parseFloat(data[i].product_price).toFixed(2)}</span>
                                </div>
                                <div class="quantity-controls">
                                    <button class="quantity-btn" onclick="event.stopPropagation(); handleQuantityChange('${itemId}', '${itemName}', -1, ${data[i].product_price}, '${imageUrl}')">−</button>
                                    <span class="quantity-value" id="quantity_${itemId}">0</span>
                                    <button class="quantity-btn" onclick="event.stopPropagation(); handleQuantityChange('${itemId}', '${itemName}', 1, ${data[i].product_price}, '${imageUrl}')">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            }
        } else {
            html = '<div class="col-12"><p class="text-center">No Items Found</p></div>';
        }
        document.getElementById('dynamic_item').innerHTML = html;
        
        // Update quantities from cart
        cart.forEach(item => {
            const quantityElement = document.getElementById(`quantity_${item.id}`);
            if (quantityElement) {
                quantityElement.textContent = item.quantity;
            }
        });
        
        // Update active category button
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.classList.remove('active');
            if ((category_id === 'all' && btn.textContent.includes('All Menu')) || 
                (btn.getAttribute('onclick').includes(`'${category_id}'`))) {
                btn.classList.add('active');
            }
        });
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('dynamic_item').innerHTML = '<div class="col-12"><div class="alert alert-danger">Error loading products. Please try again later.</div></div>';
    });
}

let cart = [];
let total = 0;
let cur = "<?php echo $confData['currency']; ?>";
let taxPer = parseFloat("<?php echo $confData['tax_rate']; ?>");

// Function to load cart data from localStorage
function loadCart() {
    const storedCart = localStorage.getItem('cart');
    if (storedCart) {
        cart = JSON.parse(storedCart);
        updateCart();
    }
}

// Function to save cart data to localStorage
function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function addToCart(itemId, itemName, itemPrice, itemImage) {
    const item = cart.find(cartItem => cartItem.id === itemId);

    if (item) {
        item.quantity += 1;
    } else {
        cart.push({ 
            id: itemId,
            name: itemName, 
            price: itemPrice, 
            quantity: 1,
            image: itemImage
        });
    }
    saveCart();
    updateCart();
}

function updateCart() {
    const cartItems = document.getElementById('order_item_details');
    let html = '';
    let taxAmt = 0;
    total = 0;
    
    cart.forEach(cartItem => {
        total += cartItem.price * cartItem.quantity;

        // Use the image path directly from the cart item
        const imageUrl = cartItem.image;

        html += `
        <div class="order-item">
            <div class="d-flex align-items-center" style="gap: 10px;">
                <img src="${imageUrl}" 
                     class="order-item-img" 
                     alt="${cartItem.name}"
                     onerror="this.onerror=null; this.src='asset/images/default-food.jpg';">
                <div class="order-item-info">
                    <div class="order-item-name">${cartItem.name}</div>
                    <div class="order-item-price">${cur}${parseFloat(cartItem.price).toFixed(2)}</div>
                </div>
            </div>
            <div class="quantity-controls">
                <button class="quantity-btn" onclick="changeQuantity('${cartItem.id}', ${cartItem.quantity - 1})">−</button>
                <div class="quantity-value">${cartItem.quantity}</div>
                <button class="quantity-btn" onclick="changeQuantity('${cartItem.id}', ${cartItem.quantity + 1})">+</button>
            </div>
        </div>`;
    });

    taxAmt = parseFloat(total) * taxPer / 100;
    cartItems.innerHTML = html;

    document.getElementById('order_gross_total').innerText = cur + parseFloat(total).toFixed(2);
    document.getElementById('order_taxes').innerText = cur + parseFloat(taxAmt).toFixed(2);
    total = parseFloat(total) + parseFloat(taxAmt);
    document.getElementById('order_net_total').innerText = cur + parseFloat(total).toFixed(2);

    const createOrderBtn = document.getElementById('order_btn');
    createOrderBtn.disabled = cart.length === 0;
}

function changeQuantity(itemId, newQuantity) {
    const item = cart.find(cartItem => cartItem.id === itemId);
    
    if (item) {
        if (newQuantity < 1) {
            // Remove the item if quantity is 0 or negative
            removeFromCart(itemId);
        } else {
            // Update the quantity if it's valid
            item.quantity = newQuantity;
            saveCart();
            updateCart();
        }
    }
}

function removeFromCart(itemId) {
    cart = cart.filter(cartItem => cartItem.id !== itemId);
    saveCart();
    updateCart();
}

// Load the cart from localStorage when the page is loaded
window.onload = loadCart;

function resetOrder(){
    load_category_product();
    cart = []; // Empty the cart array
    localStorage.removeItem('cart'); // Remove the cart from localStorage
    updateCart(); // Refresh the cart display
}

function createOrder() {
    const orderData = {
        order_number: 'ORD' + Date.now(),
        order_total: total,
        order_created_by: '<?php echo $_SESSION['user_id']; ?>',
        service_type: localStorage.getItem('selectedServiceType') || 'dine-in',
        items: cart.map(item => ({
            product_name: item.name,
            product_qty: item.quantity,
            product_price: item.price
        }))
    };

    fetch('order_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order created successfully! Order ID: ' + data.order_id);
            resetOrder();  // Clear the cart after creating the order
        } else {
            alert('Order creation failed: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Update the handleQuantityChange function
function handleQuantityChange(itemId, itemName, change, itemPrice, itemImage) {
    const item = cart.find(cartItem => cartItem.id === itemId);
    const newQuantity = item ? item.quantity + change : (change > 0 ? 1 : 0);
    
    if (newQuantity <= 0) {
        cart = cart.filter(cartItem => cartItem.id !== itemId);
    } else {
        if (item) {
            item.quantity = newQuantity;
        } else {
            cart.push({ 
                id: itemId,
                name: itemName, 
                price: itemPrice, 
                quantity: newQuantity,
                image: itemImage
            });
        }
    }
    
    // Update quantity display
    const quantityElement = document.getElementById(`quantity_${itemId}`);
    if (quantityElement) {
        quantityElement.textContent = newQuantity;
    }
    
    saveCart();
    updateCart();
}

// Add this new JavaScript function for handling service type selection
function setServiceType(type) {
    // Update active state of service type buttons
    document.querySelectorAll('.service-type').forEach(btn => {
        btn.classList.remove('active');
        if (btn.querySelector('span').textContent.toLowerCase() === type.replace('-', ' ')) {
            btn.classList.add('active');
        }
    });
    
    // Store selected service type
    localStorage.setItem('selectedServiceType', type);
}

// Load previously selected service type when page loads
window.addEventListener('load', function() {
    const savedType = localStorage.getItem('selectedServiceType') || 'dine-in';
    setServiceType(savedType);
});

</script>

<?php include('footer.php'); ?>