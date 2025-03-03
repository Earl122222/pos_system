<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminOrUserLogin();

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
            //{ "data": "order_total" },
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
                        <a href="print_order.php?id=${row.order_id}" class="btn btn-warning btn-sm" target="_blank">PDF</a>
                        <button type="button" class="btn btn-danger btn-sm btn_delete" data-id="${row.order_id}">X</button>
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
</script>