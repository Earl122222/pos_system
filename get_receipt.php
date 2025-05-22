<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if order ID is provided
if (!isset($_GET['id'])) {
    die('Order ID is required');
}

$order_id = $_GET['id'];

try {
    // Get order details
    $stmt = $pdo->prepare("
        SELECT 
            o.*,
            b.branch_name,
            u.user_name as cashier_name
        FROM pos_order o
        LEFT JOIN pos_branch b ON o.branch_id = b.branch_id
        LEFT JOIN pos_user u ON o.order_created_by = u.user_id
        WHERE o.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die('Order not found');
    }

    // Get order items
    $stmt = $pdo->prepare("
        SELECT 
            oi.*,
            p.product_name
        FROM pos_order_item oi
        JOIN pos_product p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format receipt content
    ?>
    <div class="receipt-content" style="font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.4;">
        <!-- Header -->
        <div class="text-center mb-3">
            <img src="assets/img/logo.png" alt="Logo" style="max-width: 60px; margin-bottom: 10px;">
            <h4 class="mb-1" style="font-size: 16px; font-weight: bold;">MORE BITES</h4>
            <p class="mb-1" style="font-size: 12px;"><?php echo htmlspecialchars($order['branch_name']); ?></p>
            <p class="mb-1" style="font-size: 11px;">123 Main St</p>
            <p class="mb-1" style="font-size: 11px;">Tel: 123-456-7890</p>
            <p class="mb-3" style="font-size: 11px;">Email: admin@restaurant.com</p>
        </div>

        <!-- Order Info -->
        <div class="mb-3" style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 8px 0;">
            <p class="mb-1">Order #: <?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?></p>
            <p class="mb-1">Date: <?php echo date('m/d/Y h:i A', strtotime($order['order_datetime'])); ?></p>
            <p class="mb-1">Service: <?php echo htmlspecialchars($order['service_type']); ?></p>
            <p class="mb-0">Payment: <?php echo htmlspecialchars($order['payment_method']); ?></p>
        </div>

        <!-- Items -->
        <div class="mb-3">
            <table style="width: 100%; font-size: 11px;">
                <thead>
                    <tr>
                        <th style="text-align: left;">Item</th>
                        <th style="text-align: right;">Qty</th>
                        <th style="text-align: right;">Price</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td style="padding: 3px 0;"><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td style="text-align: right;"><?php echo $item['quantity']; ?></td>
                        <td style="text-align: right;">₱<?php echo number_format($item['price'], 2); ?></td>
                        <td style="text-align: right;">₱<?php echo number_format($item['subtotal'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="mb-3" style="border-top: 1px dashed #000; padding-top: 8px;">
            <table style="width: 100%; font-size: 11px;">
                <tr>
                    <td style="text-align: right; padding: 3px 0;">Subtotal:</td>
                    <td style="text-align: right; width: 80px;">₱<?php echo number_format($order['order_subtotal'], 2); ?></td>
                </tr>
                <tr>
                    <td style="text-align: right; padding: 3px 0;">Tax (<?php echo $order['tax_rate']; ?>%):</td>
                    <td style="text-align: right;">₱<?php echo number_format($order['tax_amount'], 2); ?></td>
                </tr>
                <?php if ($order['discount_amount'] > 0): ?>
                <tr>
                    <td style="text-align: right; padding: 3px 0;">Discount:</td>
                    <td style="text-align: right;">-₱<?php echo number_format($order['discount_amount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td style="text-align: right; padding: 3px 0; font-weight: bold;">TOTAL:</td>
                    <td style="text-align: right; font-weight: bold;">₱<?php echo number_format($order['order_total'], 2); ?></td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="text-center" style="border-top: 1px dashed #000; padding-top: 8px;">
            <p class="mb-1">Cashier: <?php echo htmlspecialchars($order['cashier_name']); ?></p>
            <p class="mb-1">Thank you for dining with us!</p>
            <p class="mb-1">Please come again.</p>
            <p class="mb-0" style="font-size: 10px;">
                <?php echo date('m/d/Y h:i:s A'); ?>
            </p>
        </div>
    </div>
    <?php
} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}
?> 