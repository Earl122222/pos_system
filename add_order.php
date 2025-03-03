<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminOrUserLogin();

// Fetch category for the dropdown
$categorys = $pdo->query("SELECT category_id, category_name FROM pos_category WHERE category_status = 'Active'")->fetchAll(PDO::FETCH_ASSOC);

$confData = getConfigData($pdo);

include('header.php');
?>

<h1 class="mt-4">Order</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Order</li>
</ol>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><b>Item</b></div>
            <div class="card-body">
                <div class="mb-3">
                    <button type="button" class="btn btn-primary mb-2" onclick="load_category_product()">All</button>
                <?php foreach ($categorys as $category): ?>
                    <button type="button" class="btn btn-primary mb-2" onclick="load_category_product('<?php echo $category['category_id']; ?>')"><?php echo $category['category_name']; ?></button>&nbsp;&nbsp;
                <?php endforeach; ?>
                </div>
                <div class="row" id="dynamic_item">
                    
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6"><b>Order</b></div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-success btn-sm float-end" onclick="resetOrder()">New Order</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Price</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="order_item_details">

                        </tbody>
                        <tfooter>
                            <tr>
                                <td colspan="3"><b>Gross Total</b></td>
                                <td class="text-right"><b id="order_gross_total">0.00</b></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="3"><b>Taxes (<?php echo floatval($confData['tax_rate']); ?>%)</b></td>
                                <td class="text-right"><b id="order_taxes">0.00</b></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="3"><b>Net Total</b></td>
                                <td class="text-right"><b id="order_net_total">0.00</b></td>
                                <td>&nbsp;</td>
                            </tr>
                        </tfooter>
                    </table>
                </div>
            </div>
            <div class="card-footer text-center">
                <input type="button" class="btn btn-success" id="order_btn" value="Pay" onclick="createOrder()" disabled />
            </div>
        </div>
    </div>
    
</div>
<?php
include('footer.php');
?>

<script>

load_category_product();

function load_category_product(category_id = 0)
{
    fetch('order_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ category_id: category_id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error('Error:', data.error);
        } else {
            let html = '';
            if(data.length > 0){
                for(let i = 0; i < data.length; i++){
                    let product_status = (data[i].product_status === 'Available') ? `<span class="badge bg-success">${data[i].product_status}</span>` : `<span class="badge bg-danger">${data[i].product_status}</span>`;
                    let extraCode = (data[i].product_status === 'Available') ? `onclick="addToCart('${data[i].product_name}', ${data[i].product_price})" style="cursor:pointer"` : '';
                    html += `
                    <div class="col-md-2 text-center mb-3" ${extraCode}>
                        <img src="${data[i].product_image}" class="img-thumbnail img-fluid mb-2">
                        <br />
                        <span id="product_name_${data[i].product_id}">${data[i].product_name}</span><br />
                        ${product_status}
                    </div>
                    `;
                }
            } else {
                html = '<p class="text-center">No Item Found</p>';
            }
            document.getElementById('dynamic_item').innerHTML = html;
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
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

function addToCart(itemName, itemPrice) {
    const item = cart.find(cartItem => cartItem.name === itemName);

    if (item) {
        item.quantity += 1;
    } else {
        cart.push({ name: itemName, price: itemPrice, quantity: 1 });
    }
    saveCart();  // Save the updated cart to localStorage
    updateCart();
}

function updateCart() {
    const cartItems = document.getElementById('order_item_details');
    cartItems.innerHTML = '';    
    let cardHtml = '';
    let taxAmt = 0;
    total = 0;
    cart.forEach(cartItem => {
        total += cartItem.price * cartItem.quantity;

        cardHtml += `
        <tr>
            <td width="40%">${cartItem.name}</td>
            <td width="15%"><input type="number" name="product_qty[]" min="1" value="${cartItem.quantity}" oninput="changeQuantity('${cartItem.name}', this.value)" style="width:50px;"></td>
            <td width="15%">${cur}${parseFloat(cartItem.price).toFixed(2)}</td>
            <td width="20%">${cur}${parseFloat(cartItem.price * cartItem.quantity).toFixed(2)}</td>
            <td width="10%"><button type="button" class="btn btn-danger btn-sm" onclick="removeFromCart('${cartItem.name}')">x</button></td>
        </tr>
        `;
    });

    taxAmt = parseFloat(total) * taxPer / 100;

    cartItems.innerHTML = cardHtml;

    document.getElementById('order_gross_total').innerText = cur + parseFloat(total).toFixed(2);
    document.getElementById('order_taxes').innerText = cur + parseFloat(taxAmt).toFixed(2);
    total = parseFloat(total) + parseFloat(taxAmt);
    document.getElementById('order_net_total').innerText = cur + parseFloat(total).toFixed(2);

    // Enable or disable the Create Order button based on cart contents
    const createOrderBtn = document.getElementById('order_btn');
    createOrderBtn.disabled = cart.length === 0;
}

function changeQuantity(itemName, newQuantity) {
    const item = cart.find(cartItem => cartItem.name === itemName);
    
    if (item) {
        // Update the item's quantity to the new value
        item.quantity = parseInt(newQuantity, 10);
        if (newQuantity < 1) {
            // Remove the item if quantity is 0 or negative
            removeFromCart(itemName);
        } else {
            // Update the quantity if it's valid
            item.quantity = newQuantity;
        }
        saveCart();  // Save the updated cart to localStorage
        updateCart();  // Refresh the cart display
    }
}

function removeFromCart(itemName) {
    // Filter out the item to be removed from the cart array
    cart = cart.filter(cartItem => cartItem.name !== itemName);
    saveCart();  // Save the updated cart to localStorage
    // Update the cart display after removing the item
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
        order_number: 'ORD' + Date.now(), // Example order number
        order_total: total, // Example total amount
        order_created_by: '<?php echo $_SESSION['user_id']; ?>', // Example user ID (should be dynamic)
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

</script>