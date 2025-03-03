<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$confData = getConfigData($pdo);

include('header.php');
?>

<h1 class="mt-4">Product Management</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Product Management</li>
</ol>

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col col-md-6"><b>Product List</b></div>
            <div class="col col-md-6">
                <a href="add_product.php" class="btn btn-success btn-sm float-end">Add</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table id="productTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Product Name</th>
                    <th>Product Price</th>
                    <th>Status</th>
                    <th>Image</th>
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
    $('#productTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "product_ajax.php",
            "type": "GET"
        },
        "columns": [
            { "data": "product_id" },
            { "data": "category_name" },
            { "data": "product_name" },
            {
                "data": null,
                "render": function(data, type, row){
                    return `<?php echo $confData['currency']; ?>${row.product_price}`;
                }
            },
            { 
                "data": null,
                "render": function(data, type, row){
                    if(row.product_status === 'Available'){
                        return `<span class="badge bg-success">Available</span>`;
                    }
                    if(row.product_status === 'Out of Stock'){
                        return `<span class="badge bg-danger">Out of Stock</span>`;
                    }
                } 
            },
            {
                "data": null,
                "render": function(data, type, row){
                    return `<img src="${row.product_image}" class="rounded-circle" width="40" />`;
                }
            },
            {
                "data": null,
                "render": function(data, type, row){
                    return `
                    <div class="text-center">
                        <a href="edit_product.php?id=${row.product_id}" class="btn btn-warning btn-sm">Edit</a>
                        <button class="btn btn-danger btn-sm delete-btn" data-id="${row.product_id}">Delete</button>
                    </div>`;
                }
            }
        ]
    });

    // Handle Delete Button Click
    $(document).on('click', '.delete-btn', function() {
        let productId = $(this).data('id');
        if (confirm("Are you sure you want to delete this product?")) {
            $.ajax({
                url: 'delete_product.php',
                type: 'POST',
                data: { id: productId },
                success: function(response) {
                    alert(response);
                    $('#productTable').DataTable().ajax.reload();
                }
            });
        }
    });
});
</script>
