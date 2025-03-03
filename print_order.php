<?php

require 'vendor/autoload.php';

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminOrUserLogin();

use Dompdf\Dompdf;
use Dompdf\Options;

// Get order ID from query string or post data
$order_id = $_GET['id'] ?? null;

if ($order_id) {
    // Fetch order details
    $stmt = $pdo->prepare("SELECT * FROM pos_order WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch order items
    $stmt = $pdo->prepare("SELECT * FROM pos_order_item WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $confData = getConfigData($pdo);

    // Initialize DOMPDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $dompdf = new Dompdf($options);

    // Generate HTML content
    $html = '<html><body>';
    $html .= '<h1>Order Invoice</h1>';
    $html .= '<p><strong>Order Number:</strong> ' . htmlspecialchars($order['order_number']) . '</p>';
    $html .= '<p><strong>Date:</strong> ' . htmlspecialchars($order['order_datetime']) . '</p>';
    $html .= '<p><strong>Total Amount:</strong> ' . $confData['currency'] . htmlspecialchars($order['order_total']) . '</p>';

    // Add order items table
    $html .= '<h2>Items</h2>';
    $html .= '<table border="1" bordercolor="#000" cellpadding="5" style="width:100%; border-collapse:collapse;">';
    $html .= '<thead><tr><th>Product Name</th><th>Quantity</th><th>Price</th><th>Total</th></tr></thead>';
    $html .= '<tbody>';
    $grossTotal = 0;
    foreach ($items as $item) {
        $total_price = $item['product_qty'] * $item['product_price'];
        $grossTotal += $total_price;
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($item['product_name']) . '</td>';
        $html .= '<td>' . htmlspecialchars($item['product_qty']) . '</td>';
        $html .= '<td align="right">' . $confData['currency'] . htmlspecialchars($item['product_price']) . '</td>';
        $html .= '<td align="right">' . $confData['currency'] . number_format(htmlspecialchars($total_price), 2) . '</td>';
        $html .= '</tr>';
    }
    $html .= '
    <tr>
        <td colspan="3"><b>Gross Total</b></td>
        <td align="right">'.$confData['currency'] . number_format($grossTotal, 2).'</td>
    </tr>
    <tr>
        <td colspan="3"><b>Taxes ('.floatval($confData['tax_rate']).'%)</b></td>
        <td align="right">'.$confData['currency'] . number_format(floatval($grossTotal) * floatval($confData['tax_rate']) / 100, 2).'</td>
    </tr>
    <tr>
        <td colspan="3"><b>Gross Total</b></td>
        <td align="right">'.$confData['currency'] . $order['order_total'].'</td>
    </tr>
    ';
    $html .= '</tbody></table>';
    $html .= '</body></html>';

    // Load HTML content into DOMPDF
    $dompdf->loadHtml($html);

    // Set paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render PDF (first pass)
    $dompdf->render();

    // Stream the PDF to the browser
    $dompdf->stream("invoice_".$order['order_number'].".pdf", array("Attachment" => false)); // 'Attachment' => false to display in the browser
}

?>