<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkCashierLogin();

$confData = getConfigData($pdo);

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Order History</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="add_order.php">Sales</a></li>
        <li class="breadcrumb-item active">Order History</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <i class="fas fa-history me-1"></i>
                    Order History
                </div>
                <div class="col-auto">
                    <div class="date-filter d-flex gap-2">
                        <input type="date" id="startDate" class="form-control form-control-sm">
                        <input type="date" id="endDate" class="form-control form-control-sm">
                        <button id="filterBtn" class="btn btn-primary btn-sm">Filter</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table id="orderHistoryTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Order Number</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Set default dates
    const today = new Date();
    const thirtyDaysAgo = new Date(today);
    thirtyDaysAgo.setDate(today.getDate() - 30);
    
    $('#startDate').val(thirtyDaysAgo.toISOString().split('T')[0]);
    $('#endDate').val(today.toISOString().split('T')[0]);

    // Initialize DataTable
    const table = $('#orderHistoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'order_history_ajax.php',
            type: 'POST',
            data: function(d) {
                d.start_date = $('#startDate').val();
                d.end_date = $('#endDate').val();
            }
        },
        columns: [
            { 
                data: 'order_datetime',
                render: function(data) {
                    return new Date(data).toLocaleDateString();
                }
            },
            { 
                data: 'order_datetime',
                render: function(data) {
                    return new Date(data).toLocaleTimeString();
                }
            },
            { data: 'order_number' },
            { data: 'items' },
            { 
                data: 'order_total',
                render: function(data) {
                    return '<?php echo $confData['currency']; ?>' + parseFloat(data).toFixed(2);
                }
            },
            {
                data: 'order_id',
                render: function(data) {
                    return `<a href="print_order.php?id=${data}" class="btn btn-sm btn-primary" target="_blank">
                                <i class="fas fa-print"></i> Print
                           </a>`;
                }
            }
        ],
        order: [[0, 'desc'], [1, 'desc']],
        pageLength: 25,
        responsive: true
    });

    // Apply date filter
    $('#filterBtn').click(function() {
        table.ajax.reload();
    });
});
</script>

<?php include('footer.php'); ?> 