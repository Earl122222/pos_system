<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkOrderAccess();

$confData = getConfigData($pdo);

include('header.php');
?>

<h1 class="mt-4">Order Management</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Order Management</li>
</ol>

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col col-md-6"><b>Order List</b></div>
            <div class="col col-md-6">
                <a href="add_order.php" class="btn btn-success btn-sm float-end">Add</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table id="orderTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Order Total</th>
                    <th>Created By</th>
                    <th>Date Time</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Replace the existing modal with this enhanced version -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="receiptModalLabel">
                    <i class="fas fa-receipt me-2"></i>Print Preview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="receipt-wrapper bg-light p-3">
                    <div id="receiptContent" class="bg-white p-3 rounded shadow-sm" style="width: 80mm; margin: 0 auto;">
                        <!-- Receipt content will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">
                    <i class="fas fa-print me-2"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add this modal HTML before the closing body tag -->
<div class="modal fade" id="receiptPreviewModal" tabindex="-1" aria-labelledby="receiptPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="receiptPreviewModalLabel">
                    <i class="fas fa-receipt me-2"></i>Receipt Preview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="receipt-wrapper bg-light p-3">
                    <div id="modalReceiptContent" class="bg-white p-3 rounded shadow-sm" style="width: 80mm; margin: 0 auto;">
                        <!-- Receipt content will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="proceedToPrint()">
                    <i class="fas fa-print me-2"></i>Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Enhanced Modal Styles */
#receiptModal .modal-content {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

#receiptModal .modal-header {
    background: linear-gradient(135deg, #8B4543 0%, #6b3432 100%);
    border-bottom: none;
    padding: 1.5rem;
}

#receiptModal .modal-title {
    font-size: 1.25rem;
    font-weight: 600;
}

#receiptModal .modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

#receiptModal .receipt-wrapper {
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

#receiptModal .btn {
    padding: 0.5rem 1.5rem;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s ease;
}

#receiptModal .btn-primary {
    background-color: #8B4543;
    border-color: #8B4543;
}

#receiptModal .btn-primary:hover {
    background-color: #6b3432;
    border-color: #6b3432;
    transform: translateY(-1px);
}

#receiptModal .btn-secondary:hover {
    transform: translateY(-1px);
}

/* Print Styles */
@media print {
    body * {
        visibility: hidden;
    }
    #receiptContent, #receiptContent * {
        visibility: visible;
    }
    #receiptContent {
        position: absolute;
        left: 0;
        top: 0;
        width: 80mm !important;
        padding: 0 !important;
        margin: 0 !important;
    }
}

/* Modal Styles */
#receiptPreviewModal .modal-content {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

#receiptPreviewModal .modal-header {
    background: linear-gradient(135deg, #8B4543 0%, #6b3432 100%);
    border-bottom: none;
    padding: 1.5rem;
}

#receiptPreviewModal .modal-title {
    font-size: 1.25rem;
    font-weight: 600;
}

#receiptPreviewModal .modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

#receiptPreviewModal .receipt-wrapper {
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

#receiptPreviewModal .btn {
    padding: 0.5rem 1.5rem;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s ease;
}

#receiptPreviewModal .btn-primary {
    background-color: #8B4543;
    border-color: #8B4543;
}

#receiptPreviewModal .btn-primary:hover {
    background-color: #6b3432;
    border-color: #6b3432;
    transform: translateY(-1px);
}

#receiptPreviewModal .btn-secondary:hover {
    transform: translateY(-1px);
}
</style>

<?php
include('footer.php');
?>

<script>
$(document).ready(function() {
    $('#orderTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "order_ajax.php?action=get",
            "type": "GET"
        },
        "columns": [
            { "data": "order_number" },
            {
                "data" : null,
                "render" : function(data, type, row){
                    return `<?php echo $confData['currency']; ?>${row.order_total}`;
                }
            },
            { "data": "user_name" },
            { "data": "order_datetime" },
            {
                "data" : null,
                "render" : function(data, type, row){
                    return `
                    <div class="text-center">
                        <button type="button" class="btn btn-warning btn-sm" onclick="showReceipt(${row.order_id})">
                            <i class="fas fa-print"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm btn_delete" data-id="${row.order_id}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>`;
                }
            }
        ]
    });

    $(document).on('click', '.btn_delete', function() {
        if(confirm("Are you sure you want to remove this Order?")){
            let id = $(this).data('id');
            $.ajax({
                url : 'order_ajax.php',
                method : 'POST',
                data : {id : id},
                success:function(data){
                    $('#orderTable').DataTable().ajax.reload();
                }
            });
        }
    });
});

function showReceipt(orderId) {
    // Show loading state in modal
    const modalReceiptContent = document.getElementById('modalReceiptContent');
    modalReceiptContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading receipt...</p>
        </div>
    `;
    
    // Show modal
    const receiptModal = new bootstrap.Modal(document.getElementById('receiptPreviewModal'));
    receiptModal.show();
    
    // Load receipt content
    fetch('get_receipt.php?id=' + orderId)
        .then(response => response.text())
        .then(html => {
            modalReceiptContent.innerHTML = html;
        })
        .catch(error => {
            modalReceiptContent.innerHTML = `
                <div class="text-center py-5 text-danger">
                    <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                    <p>Error loading receipt. Please try again.</p>
                </div>
            `;
            console.error('Error:', error);
        });
}

function printReceipt() {
    const printContent = document.getElementById('receiptContent').innerHTML;
    const originalContent = document.body.innerHTML;
    
    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    printWindow.document.open();
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print Receipt</title>
            <style>
                @page {
                    size: 80mm auto;
                    margin: 0;
                }
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 10px;
                    width: 80mm;
                }
                /* Add any additional print styles here */
            </style>
        </head>
        <body>
            ${printContent}
        </body>
        </html>
    `);
    printWindow.document.close();
    
    // Wait for images to load before printing
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

// Function to proceed to print page
function proceedToPrint() {
    const urlParams = new URLSearchParams(window.location.search);
    const orderId = urlParams.get('id');
    if (orderId) {
        window.location.href = 'print_order.php?id=' + orderId;
    }
}
</script>