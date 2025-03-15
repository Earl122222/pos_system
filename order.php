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

<!-- Add this modal HTML before the closing body tag -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptModalLabel">Print Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="receiptContent">
                <!-- Receipt content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">Print</button>
            </div>
        </div>
    </div>
</div>

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
    // Load receipt content via AJAX
    $.get('get_receipt.php', { id: orderId }, function(response) {
        $('#receiptContent').html(response);
        var receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
        receiptModal.show();
    });
}

function printReceipt() {
    var printContents = document.getElementById('receiptContent').innerHTML;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    
    // Reinitialize the modal and DataTable after printing
    var receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
    $('#orderTable').DataTable();
}
</script>