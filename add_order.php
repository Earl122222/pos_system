<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkOrderAccess();

// Fetch category for the dropdown
$categorys = $pdo->query("SELECT category_id, category_name FROM pos_category WHERE category_status = 'Active'")->fetchAll(PDO::FETCH_ASSOC);

$confData = getConfigData($pdo);

include('header.php');
?>

<!-- Add the custom CSS file -->
<link rel="stylesheet" href="asset/css/order-custom.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="container-fluid px-4">
    <?php if ($_SESSION['user_type'] === 'Cashier'): ?>
        <h1 class="mt-4">Cashier Dashboard</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active">Create Order</li>
        </ol>
    <?php else: ?>
        <h1 class="mt-4">Create Order</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Create Order</li>
        </ol>
    <?php endif; ?>

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
                    <h5 class="order-title">Order Details</h5>
                    <div class="order-info">
                        <p class="mb-1">Order #: ORD-<?php echo date('Ymd-His'); ?></p>
                        <p class="mb-1">Date: <?php echo date('Y-m-d H:i:s'); ?></p>
                        <p class="mb-1">Cashier: <?php echo $_SESSION['user_name']; ?></p>
                    </div>
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

                <div class="order-items-header">
                    <div class="row mx-0 py-2 bg-light border-bottom">
                        <div class="col-6">Item</div>
                        <div class="col-2 text-center">Qty</div>
                        <div class="col-4 text-end">Total</div>
                    </div>
                </div>

                <div class="order-items" id="order_item_details">
                    <!-- Order items will be loaded here -->
                </div>

                <div class="payment-summary">
                    <div class="summary-row">
                        <span>Items Count</span>
                        <span id="items_count">0</span>
                    </div>
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

                <!-- Add Cash Payment Input Section -->
                <div id="cash-payment-section" class="mx-3 mb-3" style="display: none;">
                    <div class="payment-input-group">
                        <label>Amount Due</label>
                        <div class="payment-input">
                            <span class="currency">₱</span>
                            <input type="text" id="amount-due" readonly>
                        </div>
                    </div>
                    <div class="payment-input-group">
                        <label>Cash Amount</label>
                        <div class="payment-input">
                            <span class="currency">₱</span>
                            <input type="number" id="cash-amount" placeholder="0.00" oninput="calculateChange()">
                        </div>
                    </div>
                    <div class="payment-input-group">
                        <label>Change</label>
                        <div class="payment-input">
                            <span class="currency">₱</span>
                            <input type="text" id="change-amount" readonly>
                        </div>
                    </div>
                </div>

                <div class="payment-methods">
                    <div class="payment-method" onclick="setPaymentMethod('credit_card')">
                        <i class="fas fa-credit-card"></i>
                        <div>Credit Card</div>
                    </div>
                    <div class="payment-method" onclick="setPaymentMethod('cash')">
                        <i class="fas fa-money-bill-wave"></i>
                        <div>Cash</div>
                    </div>
                    <div class="payment-method" onclick="setPaymentMethod('e_wallet')">
                        <i class="fas fa-qrcode"></i>
                        <div>E-Wallet</div>
                    </div>
                </div>

                <div class="d-flex gap-2 mx-3 mb-3">
                    <button type="button" class="btn-place-order flex-grow-1" id="order_btn" onclick="createOrder()">
                        Take Order
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Preview Area -->
    <div class="row mt-4" id="preview-area" style="display: none;">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>Receipt Preview
                    </h5>
                </div>
                <div class="card-body" style="background: #f8f9fa;">
                    <div class="preview-container" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <div id="preview-content" style="max-width: 400px; margin: 0 auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="print-area" class="d-none">
    <!-- Print template will be populated by JavaScript -->
</div>

<!-- Add Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="alertModalLabel">
                    <i class="fas fa-exclamation-circle me-2"></i>Order Alert
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <i class="fas fa-shopping-cart text-danger" style="font-size: 48px;"></i>
                </div>
                <p class="text-center mb-0" style="font-size: 18px; color: #2c3e50;">
                    Please add items to the cart before placing an order.
                </p>
            </div>
            <div class="modal-footer justify-content-center border-0 pb-4">
                <button type="button" class="btn btn-danger px-4 py-2" data-bs-dismiss="modal">
                    <i class="fas fa-check me-2"></i>OK
                </button>
            </div>
        </div>
    </div>
</div>

<div id="print-preview-modal" class="modal fade" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt me-2"></i>Print Preview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="hidePreview()"></button>
            </div>
            <div class="modal-body p-4" style="background: #f8f9fa;">
                <div class="preview-container" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div id="print-preview-content" style="max-width: 400px; margin: 0 auto;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="hidePreview()" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <button type="button" class="btn btn-primary" onclick="confirmPrint()">
                    <i class="fas fa-print me-2"></i>Save as PDF
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@import url('https://fonts.cdnfonts.com/css/cooper-black');
.btn-print {
    padding: 15px;
    border: none;
    border-radius: 12px;
    background: #28a745;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-print:hover {
    background: #218838;
    transform: translateY(-2px);
}

.btn-place-order {
    padding: 18px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(to right, #4169e1, #6c8dff);
    color: white;
    font-weight: 600;
    font-size: 16px;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(65, 105, 225, 0.2);
}

.btn-place-order:hover {
    background: linear-gradient(to right, #3154c4, #5c7df2);
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(65, 105, 225, 0.3);
}

.btn-place-order:disabled {
    background: linear-gradient(to right, #cbd5e0, #e2e8f0);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

@media print {
    body * {
        visibility: hidden;
        margin: 0;
        padding: 0;
    }
    #print-area, #print-area * {
        visibility: visible;
    }
    #print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    @page {
        size: A4;
        margin: 10mm;
    }
}

.order-info {
    padding: 10px 15px;
    background-color: #f8f9fa;
    border-radius: 8px;
    margin: 10px 15px;
    font-size: 0.9rem;
}

.order-info p {
    margin-bottom: 5px;
    color: #666;
}

.order-items-header {
    background-color: #f8f9fa;
    font-weight: 500;
    color: #333;
    border-bottom: 1px solid #dee2e6;
}

.order-item {
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.order-item-info {
    flex-grow: 1;
    padding: 0 10px;
}

.order-item-name {
    font-weight: 500;
    color: #333;
    margin-bottom: 4px;
}

.order-item-price {
    color: #666;
    font-size: 0.9rem;
}

.order-item-img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
}

/* Enhanced Print Preview Styles */
#print-preview-modal .modal-dialog {
    max-width: 800px;
}

#print-preview-modal .modal-body {
    max-height: 80vh;
    overflow-y: auto;
}

.preview-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#print-preview-content {
    min-height: 500px;
}

@media print {
    .modal {
        display: none !important;
    }
    #print-area {
        display: block !important;
    }
    .preview-container {
        box-shadow: none;
    }
}

/* Receipt Specific Print Styles */
@page {
    size: auto;
    margin: 0mm;
}

.receipt-preview {
    background: white;
    padding: 20px;
    border-radius: 8px;
}

.receipt-preview img {
    max-width: 150px;
    height: auto;
}

.receipt-preview table {
    width: 100%;
    border-collapse: collapse;
}

.receipt-preview th,
.receipt-preview td {
    padding: 8px;
    text-align: left;
}

.receipt-preview .total-row {
    font-weight: bold;
    border-top: 2px solid #dee2e6;
}

#cash-payment-section {
    background: linear-gradient(to bottom, #ffffff, #f8f9fa);
    border-radius: 15px;
    padding: 25px;
    margin: 20px 15px 10px 15px;
    border: 2px solid #e1e7ef;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.payment-input-group {
    margin-bottom: 15px;
    position: relative;
}

.payment-input-group:last-child {
    margin-bottom: 5px;
}

.payment-input-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.payment-input {
    display: flex;
    align-items: center;
    background: white;
    border: 2px solid #e1e7ef;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.payment-input:focus-within {
    border-color: #4169e1;
    box-shadow: 0 0 0 3px rgba(65, 105, 225, 0.15);
}

.payment-input .currency {
    padding: 15px 18px;
    background: #f8f9fa;
    color: #2c3e50;
    font-weight: 600;
    font-size: 16px;
    border-right: 2px solid #e1e7ef;
}

.payment-input input {
    flex: 1;
    border: none;
    padding: 15px 18px;
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    width: 100%;
    outline: none;
    background: white;
}

#amount-due, #change-amount {
    background: #f8f9fa;
    color: #2c3e50;
    font-weight: 700;
    letter-spacing: 0.5px;
}

#cash-amount {
    background: white !important;
}

#cash-amount::placeholder {
    color: #b2bec3;
    font-weight: 400;
}

/* Enhanced Payment Methods Styles */
.payment-methods {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    padding: 20px;
    margin: 10px 15px 15px 15px;
    background: linear-gradient(to bottom, #ffffff, #f8f9fa);
    border-radius: 15px;
    border: 2px solid #e1e7ef;
}

.payment-method {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px 15px;
    background: white;
    border: 2px solid #e1e7ef;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.payment-method:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(to right, #4169e1, #6c8dff);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.payment-method:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.08);
    border-color: #4169e1;
}

.payment-method:hover:before {
    opacity: 1;
}

.payment-method.active {
    background: #4169e1;
    color: white;
    border-color: #4169e1;
    box-shadow: 0 4px 12px rgba(65, 105, 225, 0.3);
}

.payment-method.active:before {
    opacity: 1;
    background: linear-gradient(to right, #ffffff33, #ffffff66);
}

.payment-method i {
    font-size: 28px;
    margin-bottom: 10px;
    transition: transform 0.3s ease;
}

.payment-method:hover i {
    transform: scale(1.1);
}

.payment-method div {
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* Amount Status Colors */
#cash-amount[value=""] {
    color: #2c3e50;
}

#cash-amount[value]:not([value=""]) {
    color: #2c3e50;
    font-weight: 700;
}

#cash-amount.insufficient {
    color: #e74c3c !important;
}

#cash-amount.sufficient {
    color: #27ae60 !important;
}
</style>

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
    let itemCount = 0;
    
    cart.forEach(cartItem => {
        total += cartItem.price * cartItem.quantity;
        itemCount += cartItem.quantity;

        const itemTotal = cartItem.price * cartItem.quantity;
        html += `
        <div class="order-item">
            <div class="d-flex align-items-center flex-grow-1">
                <img src="${cartItem.image}" 
                     class="order-item-img" 
                     alt="${cartItem.name}"
                     onerror="this.onerror=null; this.src='asset/images/default-food.jpg';">
                <div class="order-item-info">
                    <div class="order-item-name">${cartItem.name}</div>
                    <div class="order-item-price">${cur}${parseFloat(cartItem.price).toFixed(2)} × ${cartItem.quantity}</div>
                </div>
                <div class="text-end" style="min-width: 80px;">
                    ${cur}${parseFloat(itemTotal).toFixed(2)}
                </div>
            </div>
            <div class="quantity-controls ms-2">
                <button class="quantity-btn" onclick="changeQuantity('${cartItem.id}', ${cartItem.quantity - 1})">−</button>
                <div class="quantity-value">${cartItem.quantity}</div>
                <button class="quantity-btn" onclick="changeQuantity('${cartItem.id}', ${cartItem.quantity + 1})">+</button>
            </div>
        </div>`;
    });

    taxAmt = parseFloat(total) * taxPer / 100;
    cartItems.innerHTML = html || '<div class="text-center py-4 text-muted">No items in cart</div>';

    document.getElementById('items_count').innerText = itemCount;
    document.getElementById('order_gross_total').innerText = cur + parseFloat(total).toFixed(2);
    document.getElementById('order_taxes').innerText = cur + parseFloat(taxAmt).toFixed(2);
    total = parseFloat(total) + parseFloat(taxAmt);
    document.getElementById('order_net_total').innerText = cur + parseFloat(total).toFixed(2);

    // Update amount due in cash payment section if it's visible
    const cashSection = document.getElementById('cash-payment-section');
    if (cashSection.style.display === 'block') {
        document.getElementById('amount-due').value = parseFloat(total).toFixed(2);
        calculateChange(); // Recalculate change
    }
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
    document.getElementById('preview-area').style.display = 'none'; // Hide the preview area
}

// Update the setPaymentMethod function
function setPaymentMethod(method) {
    // Update active state of payment method buttons
    document.querySelectorAll('.payment-method').forEach(btn => {
        btn.classList.remove('active');
        if (btn.textContent.trim().toLowerCase() === method.replace('_', ' ')) {
            btn.classList.add('active');
        }
    });
    
    // Show/hide cash payment section and handle input states
    const cashSection = document.getElementById('cash-payment-section');
    const orderBtn = document.getElementById('order_btn');
    
    if (method === 'cash') {
        cashSection.style.display = 'block';
        const amountDue = document.getElementById('order_net_total').innerText.replace(/[^\d.]/g, '');
        document.getElementById('amount-due').value = parseFloat(amountDue).toFixed(2);
        document.getElementById('cash-amount').value = '';
        document.getElementById('cash-amount').classList.remove('insufficient', 'sufficient');
        document.getElementById('change-amount').value = '0.00';
        setTimeout(() => document.getElementById('cash-amount').focus(), 100);
        orderBtn.disabled = true;
    } else {
        cashSection.style.display = 'none';
        orderBtn.disabled = false;
    }
    
    // Store selected payment method
    localStorage.setItem('selectedPaymentMethod', method);
}

// Update the calculateChange function
function calculateChange() {
    const amountDue = parseFloat(document.getElementById('amount-due').value) || 0;
    const cashAmount = parseFloat(document.getElementById('cash-amount').value) || 0;
    const change = cashAmount - amountDue;
    
    document.getElementById('change-amount').value = change >= 0 ? change.toFixed(2) : '0.00';
    
    // Enable/disable the order button based on cash amount
    const orderBtn = document.getElementById('order_btn');
    orderBtn.disabled = cashAmount < amountDue;
    
    // Add visual feedback
    const cashInput = document.getElementById('cash-amount');
    cashInput.classList.remove('insufficient', 'sufficient');
    
    if (cashAmount === 0) {
        // No styling for empty or 0
        cashInput.style.color = '';
    } else if (cashAmount < amountDue) {
        cashInput.classList.add('insufficient');
    } else {
        cashInput.classList.add('sufficient');
    }
}

function createOrder() {
    // Check if cart is empty
    if (cart.length === 0) {
        // Show the custom alert modal instead of using browser alert
        const alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
        alertModal.show();
        return;
    }

    // Check if cash payment has sufficient amount
    const paymentMethod = localStorage.getItem('selectedPaymentMethod');
    if (paymentMethod === 'cash') {
        const amountDue = parseFloat(document.getElementById('amount-due').value);
        const cashAmount = parseFloat(document.getElementById('cash-amount').value) || 0;
        
        if (cashAmount < amountDue) {
            alert('Please enter sufficient cash amount.');
            document.getElementById('cash-amount').focus();
            return;
        }
    }

    // Show print preview
    showPrintPreview();
}

function showPrintPreview() {
    const previewContent = generateReceiptHTML();
    
    // Set the preview content in both places
    document.getElementById('print-preview-content').innerHTML = previewContent;
    document.getElementById('preview-content').innerHTML = previewContent;
    
    // Show both the preview area and modal
    document.getElementById('preview-area').style.display = 'block';
    const modal = new bootstrap.Modal(document.getElementById('print-preview-modal'));
    modal.show();
}

function confirmPrint() {
    // Hide the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('print-preview-modal'));
    modal.hide();
    
    // Set the print area content
    const printArea = document.getElementById('print-area');
    printArea.innerHTML = generateReceiptHTML();
    
    // Print after a short delay to ensure content is rendered
    setTimeout(() => {
        window.print();
        // Clear the cart and hide preview after printing
        resetOrder();
    }, 100);
}

function generateReceiptHTML() {
    const serviceType = localStorage.getItem('selectedServiceType') || 'dine-in';
    const paymentMethod = localStorage.getItem('selectedPaymentMethod') || 'credit_card';
    const orderNumber = 'ORD' + Date.now();
    const date = new Date().toLocaleString();
    let subtotal = 0;
    cart.forEach(item => {
        subtotal += item.price * item.quantity;
    });
    const tax = subtotal * taxPer / 100;
    const total = subtotal + tax;

    // Get cash payment details if payment method is cash
    const cashAmount = paymentMethod === 'cash' ? parseFloat(document.getElementById('cash-amount').value) || 0 : 0;
    const change = paymentMethod === 'cash' ? parseFloat(document.getElementById('change-amount').value) || 0 : 0;

    let itemsHtml = '';
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        itemsHtml += `
            <tr>
                <td style="width: 60%; padding: 12px; border-bottom: 1px dashed #ff0000;">
                    <div style="font-weight: 600; color: #2c3e50; font-size: 14px; letter-spacing: 0.5px;">
                        ${item.name}
                    </div>
                    <div style="font-size: 12px; color: #7f8c8d; margin-top: 4px; font-style: italic;">
                        ${cur}${parseFloat(item.price).toFixed(2)} × ${item.quantity}
                    </div>
                </td>
                <td style="width: 40%; text-align: right; padding: 12px; border-bottom: 1px dashed #ff0000;">
                    <strong style="font-size: 15px; color: #2c3e50;">${cur}${parseFloat(itemTotal).toFixed(2)}</strong>
                </td>
            </tr>
        `;
    });

    // Create the payment details HTML based on payment method
    const paymentDetailsHtml = paymentMethod === 'cash' ? `
        <tr style="font-size: 13px;">
            <td style="padding: 5px 0; text-align: right; color: #34495e; letter-spacing: 0.5px;">Cash Amount:</td>
            <td style="padding: 5px 0; text-align: right; color: #34495e; letter-spacing: 0.5px;">
                ${cur}${parseFloat(cashAmount).toFixed(2)}
            </td>
        </tr>
        <tr style="font-size: 13px;">
            <td style="padding: 5px 0; text-align: right; color: #34495e; letter-spacing: 0.5px;">Change:</td>
            <td style="padding: 5px 0; text-align: right; color: #34495e; letter-spacing: 0.5px;">
                ${cur}${parseFloat(change).toFixed(2)}
            </td>
        </tr>
    ` : '';

    return `
        <div style="width: 80mm; margin: 0 auto; padding: 20px; font-family: 'Arial', sans-serif; color: #2c3e50; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
            <!-- Header Section -->
            <div style="text-align: center; padding-bottom: 20px; border-bottom: 2px dashed #ff0000;">
                <img src="asset/images/logo.png" alt="Restaurant Logo" style="width: 150px; height: auto; margin-bottom: 15px;">
                <h1 style="font-size: 28px; font-weight: 700; margin: 10px 0; text-transform: uppercase; font-family: 'Cooper Black', sans-serif; color: #2c3e50; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);">
                    More Bites
                </h1>
                <div style="font-size: 13px; color: #34495e; line-height: 1.6;">
                    <p style="margin: 4px 0; font-weight: 500; letter-spacing: 0.5px;">Corner Tiano Del Pilar Street</p>
                    <p style="margin: 4px 0; font-weight: 500; letter-spacing: 0.5px;">Cagayan de Oro, Philippines</p>
                    <p style="margin: 4px 0; letter-spacing: 0.5px;">Email: vegaforcecdo@yahoo.com</p>
                    <p style="margin: 4px 0; letter-spacing: 0.5px;">Tel: 0906 933 3624</p>
                </div>
            </div>

            <!-- Order Info Section -->
            <div style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 10px; font-size: 13px; border: 1px dashed #ff0000;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 5px 0; color: #34495e;"><strong style="letter-spacing: 0.5px;">Order #:</strong></td>
                        <td style="padding: 5px 0; color: #34495e; text-align: right; letter-spacing: 0.5px;">${orderNumber}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #34495e;"><strong style="letter-spacing: 0.5px;">Date:</strong></td>
                        <td style="padding: 5px 0; color: #34495e; text-align: right; letter-spacing: 0.5px;">${date}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #34495e;"><strong style="letter-spacing: 0.5px;">Service:</strong></td>
                        <td style="padding: 5px 0; color: #34495e; text-align: right; letter-spacing: 0.5px;">${serviceType.replace('-', ' ').toUpperCase()}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #34495e;"><strong style="letter-spacing: 0.5px;">Payment:</strong></td>
                        <td style="padding: 5px 0; color: #34495e; text-align: right; letter-spacing: 0.5px;">${paymentMethod.replace('_', ' ').toUpperCase()}</td>
                    </tr>
                </table>
            </div>

            <!-- Order Items Section -->
            <div style="margin: 20px 0; border: 1px dashed #ff0000; border-radius: 10px; overflow: hidden; background: white;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background: #fff0f0;">
                            <th style="padding: 15px; text-align: left; border-bottom: 2px dashed #ff0000; letter-spacing: 1px;">Item</th>
                            <th style="padding: 15px; text-align: right; border-bottom: 2px dashed #ff0000; letter-spacing: 1px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                </table>
            </div>

            <!-- Totals Section -->
            <div style="margin: 20px 0; background: #fff0f0; border-radius: 10px; padding: 15px; border: 1px dashed #ff0000;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="font-size: 13px;">
                        <td style="padding: 5px 0; text-align: right; color: #34495e; letter-spacing: 0.5px;">Subtotal:</td>
                        <td style="padding: 5px 0; text-align: right; width: 100px; color: #34495e; letter-spacing: 0.5px;">
                            ${cur}${parseFloat(subtotal).toFixed(2)}
                        </td>
                    </tr>
                    <tr style="font-size: 13px;">
                        <td style="padding: 5px 0; text-align: right; color: #34495e; letter-spacing: 0.5px;">Tax (${taxPer}%):</td>
                        <td style="padding: 5px 0; text-align: right; color: #34495e; letter-spacing: 0.5px;">
                            ${cur}${parseFloat(tax).toFixed(2)}
                        </td>
                    </tr>
                    <tr style="font-size: 18px; font-weight: bold;">
                        <td style="padding: 10px 0; text-align: right; border-top: 2px dashed #ff0000; color: #2c3e50; letter-spacing: 1px;">
                            TOTAL:
                        </td>
                        <td style="padding: 10px 0; text-align: right; border-top: 2px dashed #ff0000; color: #2c3e50; letter-spacing: 0.5px;">
                            ${cur}${parseFloat(total).toFixed(2)}
                        </td>
                    </tr>
                    ${paymentDetailsHtml}
                </table>
            </div>

            <!-- Footer Section -->
            <div style="margin-top: 20px; text-align: center; border-top: 2px dashed #ff0000; padding-top: 20px;">
                <div style="background: #fff0f0; border-radius: 10px; margin: 15px 0; padding: 10px; font-family: monospace; font-size: 14px; border: 1px dashed #ff0000; letter-spacing: 1px;">
                    ${orderNumber}
                </div>
                <div style="font-size: 22px; font-weight: 600; margin: 15px 0; color: #2c3e50; font-family: 'Cooper Black', sans-serif; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);">Thank You!</div>
                <p style="font-size: 13px; color: #34495e; margin: 5px 0; letter-spacing: 0.5px;">Please come again!</p>
                <p style="font-size: 11px; color: #7f8c8d; margin-top: 15px; font-style: italic; letter-spacing: 0.5px;">
                    Printed: ${new Date().toLocaleString()}
                </p>
            </div>
        </div>
    `;
}

// Load payment method when page loads
window.addEventListener('load', function() {
    const savedPaymentMethod = localStorage.getItem('selectedPaymentMethod') || 'credit_card';
    setPaymentMethod(savedPaymentMethod);
    loadCart();
});

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

// Add event listener for when the modal is closed
document.addEventListener('DOMContentLoaded', function() {
    const previewModal = document.getElementById('print-preview-modal');
    previewModal.addEventListener('hidden.bs.modal', function () {
        // Hide the preview area when modal is closed
        document.getElementById('preview-area').style.display = 'none';
    });
});

// Add hidePreview function
function hidePreview() {
    document.getElementById('preview-area').style.display = 'none';
}

</script>

<?php include('footer.php'); ?>