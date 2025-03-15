<?php

require 'vendor/autoload.php';

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

<!-- Print Preview Modal -->
<div class="modal fade" id="printPreviewModal" tabindex="-1" aria-labelledby="printPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="printPreviewModalLabel">Print Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
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
                        <div class="order-row">
                            <span class="order-label">Order #:</span>
                            <span class="order-value"><?php echo htmlspecialchars($order['order_number']); ?></span>
                        </div>
                        <div class="order-row">
                            <span class="order-label">Date:</span>
                            <span class="order-value"><?php echo date('n/d/Y, h:i:s A', strtotime($order['order_datetime'])); ?></span>
                        </div>
                        <div class="order-row">
                            <span class="order-label">Service:</span>
                            <span class="order-value"><?php echo strtoupper($order['service_type']); ?></span>
                        </div>
                        <div class="order-row">
                            <span class="order-label">Payment:</span>
                            <span class="order-value"><?php echo strtoupper(str_replace('_', ' ', $order['payment_method'])); ?></span>
                        </div>
                    </div>

                    <div class="items-section">
                        <div class="items-header">
                            <span>Item</span>
                            <span>Amount</span>
                        </div>
                        <?php foreach ($items as $item): ?>
                        <div class="item-row">
                            <div class="item-details">
                                <?php echo htmlspecialchars($item['product_name']); ?><br>
                                <small><?php echo $confData['currency'] . number_format($item['product_price'], 2) . ' × ' . $item['product_qty']; ?></small>
                            </div>
                            <div class="item-amount">
                                <?php echo $confData['currency'] . number_format($item['item_total'], 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="totals-section">
                        <div class="total-row">
                            <span class="total-label">Subtotal:</span>
                            <span class="total-value"><?php echo $confData['currency'] . number_format($order['order_subtotal'], 2); ?></span>
                        </div>
                        <div class="total-row">
                            <span class="total-label">Tax (<?php echo $confData['tax_rate']; ?>%):</span>
                            <span class="total-value"><?php echo $confData['currency'] . number_format($order['order_tax'], 2); ?></span>
                        </div>
                        <?php if ($order['order_discount'] > 0): ?>
                        <div class="total-row">
                            <span class="total-label">Discount:</span>
                            <span class="total-value">-<?php echo $confData['currency'] . number_format($order['order_discount'], 2); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="total-row grand-total">
                            <span class="total-label">TOTAL:</span>
                            <span class="total-value"><?php echo $confData['currency'] . number_format($order['order_total'], 2); ?></span>
                        </div>
                    </div>

                    <div class="footer">
                        <p>Thank you for dining with us!</p>
                        <p>Please come again</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">Print</button>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-dialog {
        max-width: 400px;
    }
    .receipt {
        width: 80mm;
        padding: 10px;
        margin: 0 auto;
        background: white;
    }
    .logo {
        max-width: 60px;
        margin-bottom: 10px;
    }
    .restaurant-name {
        font-size: 20px;
        font-weight: bold;
        color: #333;
        margin: 10px 0;
        text-transform: uppercase;
    }
    .restaurant-info {
        font-size: 12px;
        color: #666;
        margin: 5px 0;
    }
    .divider {
        border-top: 1px dashed #ff0000;
        margin: 15px 0;
    }
    .order-details {
        border: 1px dashed #ff0000;
        padding: 10px;
        margin: 15px 0;
    }
    .order-row {
        display: flex;
        justify-content: space-between;
        margin: 5px 0;
    }
    .order-label {
        color: #666;
    }
    .order-value {
        font-weight: bold;
        text-align: right;
    }
    .items-section {
        border: 1px dashed #ff0000;
        padding: 10px;
        margin: 15px 0;
    }
    .items-header {
        display: flex;
        justify-content: space-between;
        color: #666;
        margin-bottom: 10px;
    }
    .item-row {
        display: flex;
        justify-content: space-between;
        margin: 5px 0;
    }
    .item-details {
        flex: 1;
    }
    .item-amount {
        text-align: right;
        font-weight: bold;
    }
    .totals-section {
        border: 1px dashed #ff0000;
        padding: 10px;
        margin: 15px 0;
        background-color: #fff5f5;
    }
    .total-row {
        display: flex;
        justify-content: space-between;
        margin: 5px 0;
    }
    .total-label {
        color: #666;
    }
    .total-value {
        font-weight: bold;
        text-align: right;
    }
    .grand-total {
        font-size: 16px;
        font-weight: bold;
        margin-top: 10px;
        padding-top: 5px;
        border-top: 1px dashed #ff0000;
    }
    .footer {
        text-align: center;
        margin-top: 20px;
        font-size: 12px;
        color: #666;
    }
    @media print {
        body * {
            visibility: hidden;
        }
        .modal {
            position: absolute;
            left: 0;
            top: 0;
            margin: 0;
            padding: 0;
            min-height: 100%;
        }
        .modal-dialog {
            transform: none !important;
            margin: 0;
        }
        .receipt, .receipt * {
            visibility: visible;
        }
        .receipt {
            position: fixed;
            left: 0;
            top: 0;
        }
        .modal-header,
        .modal-footer {
            display: none !important;
        }
    }
</style>

<script>
    // Show modal when page loads
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('printPreviewModal'));
        modal.show();
    });

    // Print function
    function printReceipt() {
        window.print();
    }
</script>