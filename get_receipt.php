<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkOrderAccess();

// Get configuration data
$confData = getConfigData($pdo);

// Get order ID from URL
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    // Get order details with cashier name
    $stmt = $pdo->prepare("
        SELECT 
            o.*,
            u.user_name as cashier_name
        FROM pos_order o
        LEFT JOIN pos_user u ON o.order_created_by = u.user_id
        WHERE o.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Order not found");
    }

    // Get order items with product names
    $stmt = $pdo->prepare("
        SELECT 
            oi.*,
            p.product_name
        FROM pos_order_item oi
        LEFT JOIN pos_product p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching order details: " . $e->getMessage());
}
?>
<div class="receipt">
    <div class="header">
        <img src="asset/images/logo.png" alt="More Bites" class="logo">
        <div class="restaurant-name">MORE BITES</div>
        <div class="restaurant-info"><?php echo htmlspecialchars($confData['restaurant_address']); ?></div>
        <div class="restaurant-info">Email: <?php echo htmlspecialchars($confData['restaurant_email']); ?></div>
        <div class="restaurant-info">Tel: <?php echo htmlspecialchars($confData['restaurant_phone']); ?></div>
    </div>

    <div class="divider"></div>

    <div class="order-details">
        <table class="details-table">
            <tr>
                <td class="label">Order #:</td>
                <td class="value"><?php echo htmlspecialchars($order['order_number']); ?></td>
            </tr>
            <tr>
                <td class="label">Date:</td>
                <td class="value"><?php echo date('n/d/Y, h:i:s A', strtotime($order['order_datetime'])); ?></td>
            </tr>
            <tr>
                <td class="label">Service:</td>
                <td class="value"><?php echo strtoupper($order['service_type']); ?></td>
            </tr>
            <tr>
                <td class="label">Payment:</td>
                <td class="value"><?php echo strtoupper(str_replace('_', ' ', $order['payment_method'])); ?></td>
            </tr>
        </table>
    </div>

    <div class="items-section">
        <table class="items-table">
            <tr class="header-row">
                <th>Item</th>
                <th class="amount">Amount</th>
            </tr>
            <?php foreach ($items as $item): ?>
            <tr class="item-row">
                <td>
                    <?php echo htmlspecialchars($item['product_name']); ?><br>
                    <span class="price-qty"><?php echo $confData['currency'] . number_format($item['product_price'], 2) . ' × ' . $item['product_qty']; ?></span>
                </td>
                <td class="amount">
                    <?php echo $confData['currency'] . number_format($item['item_total'], 2); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td>Subtotal:</td>
                <td class="amount"><?php echo $confData['currency'] . number_format($order['order_subtotal'], 2); ?></td>
            </tr>
            <tr>
                <td>Tax (<?php echo $confData['tax_rate']; ?>%):</td>
                <td class="amount"><?php echo $confData['currency'] . number_format($order['order_tax'], 2); ?></td>
            </tr>
            <?php if ($order['order_discount'] > 0): ?>
            <tr>
                <td>Discount:</td>
                <td class="amount">-<?php echo $confData['currency'] . number_format($order['order_discount'], 2); ?></td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td>TOTAL:</td>
                <td class="amount"><?php echo $confData['currency'] . number_format($order['order_total'], 2); ?></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Thank you for dining with us!</p>
        <p>Please come again</p>
    </div>
</div>

<style>
.receipt {
    font-family: Arial, sans-serif;
    width: 80mm;
    padding: 10px;
    margin: 0 auto;
    font-size: 12px;
    line-height: 1.4;
}
.header {
    text-align: center;
    margin-bottom: 10px;
}
.logo {
    width: 60px;
    margin-bottom: 5px;
}
.restaurant-name {
    font-size: 18px;
    font-weight: bold;
    margin: 5px 0;
}
.restaurant-info {
    font-size: 12px;
    color: #333;
    margin: 2px 0;
}
.divider {
    border-top: 1px dashed #ff0000;
    margin: 10px 0;
}
.order-details {
    border: 1px dashed #ff0000;
    padding: 8px;
    margin: 10px 0;
}
.details-table {
    width: 100%;
    border-collapse: collapse;
}
.details-table td {
    padding: 2px 0;
}
.details-table .label {
    color: #666;
}
.details-table .value {
    text-align: right;
    font-weight: bold;
}
.items-section {
    border: 1px dashed #ff0000;
    padding: 8px;
    margin: 10px 0;
}
.items-table {
    width: 100%;
    border-collapse: collapse;
}
.items-table th {
    text-align: left;
    color: #666;
    padding-bottom: 5px;
}
.items-table .amount {
    text-align: right;
}
.price-qty {
    font-size: 11px;
    color: #666;
}
.totals-section {
    border: 1px dashed #ff0000;
    padding: 8px;
    margin: 10px 0;
    background-color: #fff5f5;
}
.totals-table {
    width: 100%;
    border-collapse: collapse;
}
.totals-table td {
    padding: 2px 0;
}
.totals-table .amount {
    text-align: right;
}
.total-row {
    font-weight: bold;
    font-size: 14px;
    border-top: 1px dashed #ff0000;
}
.total-row td {
    padding-top: 5px;
}
.footer {
    text-align: center;
    margin-top: 15px;
    font-size: 12px;
    color: #666;
}
.footer p {
    margin: 3px 0;
}
</style> 