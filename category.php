<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

include('header.php');
?>

<h1 class="mt-4">Category Management</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Category Management</li>
</ol>

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col col-md-6"><b>Category List</b></div>
            <div class="col col-md-6">
                <a href="add_category.php" class="btn btn-success btn-sm float-end">Add</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table id="categoryTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function() {
    $('#categoryTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "category_ajax.php",
            "type": "GET"
        },
        "columns": [
            { "data": "category_id" },
            { "data": "category_name" },
            { 
                "data": null,
                "render": function(data, type, row) {
                    if(row.category_status === 'Active'){
                        return `<span class="badge bg-success">Active</span>`;
                    } else {
                        return `<span class="badge bg-danger">Inactive</span>`;
                    }
                }
            },
            {
                "data": null,
                "render": function(data, type, row) {
                    return `
                    <div class="text-center">
                        <a href="edit_category.php?id=${row.category_id}" class="btn btn-warning btn-sm">Edit</a>
                        <button class="btn btn-danger btn-sm delete-btn" data-id="${row.category_id}">Delete</button>
                    </div>`;
                }
            }
        ]
    });

    // Handle Delete Button Click
    $(document).on('click', '.delete-btn', function() {
        let categoryId = $(this).data('id');
        if (confirm("Are you sure you want to delete this category?")) {
            $.ajax({
                url: 'delete_category.php',
                type: 'POST',
                data: { id: categoryId },
                success: function(response) {
                    alert(response);
                    $('#categoryTable').DataTable().ajax.reload();
                }
            });
        }
    });
});
</script>
