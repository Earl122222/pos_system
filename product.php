<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$confData = getConfigData($pdo);

// Fetch categories for the dropdown
$stmt = $pdo->query("SELECT * FROM pos_category WHERE category_status = 'Active'");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>

<div class="container-fluid px-4">
<h1 class="mt-4" style="color: #8B4543; font-size: 1.25rem; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-weight: 500; background-color: #F8F9FA; padding: 1rem;">| Product Management</h1>
        <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-utensils me-1"></i>
                        Product List
            </div>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fas fa-plus me-1"></i> Add Product
                    </button>
    </div>
    <div class="card-body">
                    <table id="productTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Product Name</th>
                    <th>Product Price</th>
                    <th>Description</th>
                    <th>Ingredients</th>
                    <th>Status</th>
                    <th>Image</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">
                    <i class="fas fa-plus me-1"></i>
                    Add New Product
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addProductForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category:</label>
                        <select id="category_id" name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="product_name" class="form-label">Product Name:</label>
                        <input type="text" id="product_name" name="product_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_price" class="form-label">Product Price:</label>
                        <div class="input-group">
                            <span class="input-group-text"><?php echo $confData['currency']; ?></span>
                        <input type="number" id="product_price" name="product_price" class="form-control" step="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="product_image" class="form-label">Product Image:</label>
                        <input type="file" id="product_image" name="product_image" class="form-control" accept="image/*" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description:</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="ingredients" class="form-label">Ingredients:</label>
                        <textarea id="ingredients" name="ingredients" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="product_status" class="form-label">Status:</label>
                        <select id="product_status" name="product_status" class="form-select" required>
                            <option value="Available">Available</option>
                            <option value="Out of Stock">Out of Stock</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function() {
    var productTable = $('#productTable').DataTable({
        "processing": true,
        "serverSide": true,
        "pageLength": 10,
        "ajax": {
            "url": "product_ajax.php",
            "type": "GET",
            "data": function(d) {
                // If length is -1, set it to a very large number to get all records
                if (d.length === -1) {
                    d.length = 999999;
                }
                return d;
            },
            "error": function(xhr, error, thrown) {
                console.error('DataTables error:', error);
                alert('Error loading product data. Please refresh the page.');
            },
            "dataSrc": function(json) {
                // Log the response for debugging
                console.log('Server response:', json);
                return json.data;
            }
        },
        "columns": [
            { 
                "data": "product_id",
                "name": "product_id",
                "searchable": false,
                "className": "text-center"
            },
            { 
                "data": "category_name",
                "name": "category_name",
                "defaultContent": "Uncategorized"
            },
            { 
                "data": "product_name",
                "name": "product_name"
            },
            {
                "data": "product_price",
                "name": "product_price",
                "className": "text-end",
                "render": function(data, type, row) {
                    if (type === 'display') {
                        return `<?php echo $confData['currency']; ?>${parseFloat(data).toFixed(2)}`;
                    }
                    return data;
                }
            },
            { 
                "data": "description",
                "name": "description",
                "render": function(data, type, row) {
                    if (type === 'display') {
                        return data ? (data.length > 50 ? data.substr(0, 47) + '...' : data) : '-';
                    }
                    return data;
                }
            },
            { 
                "data": "ingredients",
                "name": "ingredients",
                "render": function(data, type, row) {
                    if (type === 'display') {
                        return data ? (data.length > 50 ? data.substr(0, 47) + '...' : data) : '-';
                    }
                    return data;
                }
            },
            { 
                "data": "product_status",
                "name": "product_status",
                "className": "text-center",
                "render": function(data, type, row) {
                    if (type === 'display') {
                        return data === 'Available' 
                            ? '<span class="badge bg-success">Available</span>'
                            : '<span class="badge bg-danger">Out of Stock</span>';
                    }
                    return data;
                }
            },
            {
                "data": "product_image",
                "name": "product_image",
                "searchable": false,
                "orderable": false,
                "className": "text-center",
                "render": function(data, type, row) {
                    if (type === 'display') {
                        return `<img src="${data}" class="rounded-circle" width="40" height="40" alt="Product Image" onerror="this.src='uploads/no-image.jpg'"/>`;
                    }
                    return data;
                }
            },
            {
                "data": "product_id",
                "name": "action",
                "searchable": false,
                "orderable": false,
                "className": "text-center",
                "render": function(data, type, row) {
                    if (type === 'display') {
                        return `
                        <div class="btn-group" role="group">
                            <a href="edit_product.php?id=${data}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="${data}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>`;
                    }
                    return data;
                }
            }
        ],
        "order": [[0, 'desc']],
        "pagingType": "full_numbers",
        "language": {
            "paginate": {
                "first": '<i class="fas fa-angle-double-left"></i>',
                "previous": '<i class="fas fa-angle-left"></i>',
                "next": '<i class="fas fa-angle-right"></i>',
                "last": '<i class="fas fa-angle-double-right"></i>'
            },
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "lengthMenu": "Show _MENU_ entries per page",
            "zeroRecords": "No products found",
            "infoEmpty": "No products available",
            "infoFiltered": "(filtered from _MAX_ total products)",
            "search": "Search products:",
            "processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        },
        "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
               "<'row'<'col-sm-12'tr>>" +
               "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "displayLength": 10,
        "stateSave": true,
        "responsive": true,
        "drawCallback": function(settings) {
            // Style pagination buttons
            $('.paginate_button').addClass('btn btn-sm mx-1');
            $('.paginate_button.current').addClass('btn-primary').removeClass('btn-outline-primary');
            $('.paginate_button:not(.current)').addClass('btn-outline-primary');
            $('.paginate_button.disabled').addClass('btn-outline-secondary').removeClass('btn-outline-primary');

            // Update table info text
            var api = this.api();
            var pageInfo = api.page.info();
            if(pageInfo.pages > 0) {
                $('.dataTables_info').show();
            } else {
                $('.dataTables_info').hide();
            }
        }
    });

    // Handle Add Product Form Submit
    $('#addProductForm').on('submit', function(e) {
        e.preventDefault();
        
        // Disable submit button and show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...');
        
        let formData = new FormData(this);
        
        $.ajax({
            url: 'add_product_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                let result;
                try {
                    result = typeof response === 'string' ? JSON.parse(response) : response;
                } catch(e) {
                    console.error('Error parsing response:', e);
                    alert('An error occurred while processing the response.');
                    return;
                }

                if(result.status === 'success') {
                    // Clear the form
                    $('#addProductForm')[0].reset();
                    
                    // Hide the modal
                    $('#addProductModal').modal('hide');
                    
                    // Force a complete reload of the table data
                    try {
                        productTable.ajax.reload(null, false);
                    } catch (err) {
                        console.error('Error reloading table:', err);
                        // Fallback: reload the page if table reload fails
                        window.location.reload();
                    }
                    
                    // Show success message
                    alert('Product added successfully!');
                } else {
                    alert('Error: ' + (result.message || 'Unknown error occurred'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', status, error);
                alert('An error occurred while adding the product. Please try again.');
            },
            complete: function() {
                // Re-enable submit button and restore original text
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

    // Reset form when modal is closed
    $('#addProductModal').on('hidden.bs.modal', function() {
        const form = $('#addProductForm');
        form[0].reset();
        // Re-enable any disabled buttons
        form.find('button').prop('disabled', false);
        // Clear any error messages or highlights
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').remove();
    });

    // Handle Delete Button Click
    $(document).on('click', '.delete-btn', function() {
        let productId = $(this).data('id');
        if (confirm("Are you sure you want to delete this product?")) {
            // Disable the delete button
            const deleteBtn = $(this);
            deleteBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
            
            $.ajax({
                url: 'delete_product.php',
                type: 'POST',
                data: { id: productId },
                success: function(response) {
                    alert(response);
                    try {
                        productTable.ajax.reload(null, false);
                    } catch (err) {
                        console.error('Error reloading table:', err);
                        // Fallback: reload the page if table reload fails
                        window.location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Delete error:', status, error);
                    alert('An error occurred while deleting the product. Please try again.');
                },
                complete: function() {
                    // Re-enable and restore the delete button
                    deleteBtn.prop('disabled', false).html('Delete');
                }
            });
        }
    });

    // Add error handling for DataTables ajax calls
    $.fn.dataTable.ext.errMode = 'none';
    productTable.on('error.dt', function(e, settings, techNote, message) {
        console.error('DataTables error:', message);
        alert('An error occurred while loading the product data. The page will refresh.');
        window.location.reload();
    });
});
</script>

<style>
/* Modern Card and Table Styling */
:root {
    --primary-color: #8B4543;
    --primary-dark: #723937;
    --primary-light: #A65D5D;
    --accent-color: #D4A59A;
    --text-light: #F3E9E7;
    --text-dark: #3C2A2A;
    --border-color: #C4B1B1;
    --hover-color: #F5EDED;
    --danger-color: #B33A3A;
    --success-color: #4A7C59;
    --warning-color: #C4804D;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(139, 69, 67, 0.15);
    border: none;
    border-radius: 0.75rem;
    background: #ffffff;
}

.card-header {
    background: var(--primary-color);
    color: var(--text-light);
    border-bottom: none;
    padding: 1.5rem;
    border-radius: 0.75rem 0.75rem 0 0;
}

.card-header i {
    color: var(--text-light);
}

.table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0 8px;
}

.table thead th {
    background-color: var(--hover-color);
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    color: var(--primary-color);
    padding: 1rem;
    white-space: nowrap;
}

.table tbody tr {
    background: white;
    box-shadow: 0 2px 4px rgba(139, 69, 67, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.table tbody tr:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(139, 69, 67, 0.1);
    background: var(--hover-color);
}

.table tbody td {
    padding: 1rem;
    border: none;
    background: transparent;
}

.table tbody tr td:first-child {
    border-top-left-radius: 0.5rem;
    border-bottom-left-radius: 0.5rem;
}

.table tbody tr td:last-child {
    border-top-right-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
}

/* Search and Length Menu */
.dataTables_wrapper {
    padding: 1.5rem;
}

.dataTables_length select {
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    font-size: 0.875rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    background-color: white;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%238B4543' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem;
    transition: all 0.2s ease;
}

.dataTables_length select:hover {
    border-color: var(--primary-color);
}

.dataTables_filter input {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    background-color: white;
    min-width: 300px;
    transition: all 0.2s ease;
}

.dataTables_filter input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
}

/* Pagination */
.dataTables_paginate {
    margin-top: 1.5rem;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 0.5rem;
}

.dataTables_paginate .paginate_button {
    min-width: 36px;
    height: 36px;
    padding: 0;
    margin: 0 2px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.35rem;
    border: 1px solid transparent;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-dark) !important;
    background-color: white;
    transition: all 0.2s ease;
}

.dataTables_paginate .paginate_button:hover {
    color: var(--primary-color) !important;
    background: var(--hover-color);
    border-color: var(--primary-color);
}

.dataTables_paginate .paginate_button.current {
    background: var(--primary-color);
    color: white !important;
    border-color: var(--primary-color);
    font-weight: 600;
}

.dataTables_paginate .paginate_button.disabled {
    color: var(--border-color) !important;
    border-color: var(--border-color);
    cursor: not-allowed;
}

/* Buttons */
.btn-success {
    background: var(--success-color);
    border: none;
    border-radius: 0.5rem;
    padding: 0.625rem 1.25rem;
    font-weight: 500;
    color: white;
    transition: all 0.2s ease;
}

.btn-success:hover {
    background: darken(var(--success-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(74, 124, 89, 0.15);
}

/* Status Badges */
.badge {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 0.35rem;
}

.badge.bg-success {
    background: var(--success-color) !important;
    color: white;
}

.badge.bg-danger {
    background: var(--danger-color) !important;
    color: white;
}

/* Product Images */
img.rounded-circle {
    width: 48px;
    height: 48px;
    border-radius: 0.5rem !important;
    object-fit: cover;
    border: 2px solid var(--border-color);
    transition: all 0.2s ease;
}

img.rounded-circle:hover {
    transform: scale(1.1);
    border-color: var(--primary-color);
}

/* Action Buttons */
.btn-group .btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.35rem;
    margin: 0 0.125rem;
    border: none;
    transition: all 0.2s ease;
}

.btn-group .btn-warning {
    background: var(--warning-color);
    color: white;
}

.btn-group .btn-warning:hover {
    background: darken(var(--warning-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(196, 128, 77, 0.15);
}

.btn-group .btn-danger {
    background: var(--danger-color);
    color: white;
}

.btn-group .btn-danger:hover {
    background: darken(var(--danger-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(179, 58, 58, 0.15);
}

/* Modal Styling */
.modal-content {
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(139, 69, 67, 0.15);
}

.modal-header {
    background: var(--primary-color);
    color: var(--text-light);
    border: none;
    padding: 1.5rem;
    border-radius: 0.75rem 0.75rem 0 0;
}

.modal-header .btn-close {
    color: var(--text-light);
    opacity: 0.8;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    background-color: var(--hover-color);
    border-top: 1px solid var(--border-color);
    padding: 1.25rem;
    border-radius: 0 0 0.75rem 0.75rem;
}

/* Form Controls */
.form-control, .form-select {
    border-radius: 0.5rem;
    border: 1px solid var(--border-color);
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
}

.input-group-text {
    border-radius: 0.5rem 0 0 0.5rem;
    border: 1px solid var(--border-color);
    border-right: none;
    background-color: var(--hover-color);
}

.input-group .form-control {
    border-radius: 0 0.5rem 0.5rem 0;
}

/* Loading Spinner */
.spinner-border {
    color: var(--primary-color);
}

/* Info Text */
.dataTables_info {
    color: var(--text-dark);
    font-size: 0.875rem;
    padding-top: 1.5rem;
}

/* Breadcrumb */
.breadcrumb {
    padding: 0.75rem 1rem;
    background: var(--hover-color);
    border-radius: 0.35rem;
    margin-bottom: 1.5rem;
}

.breadcrumb-item a {
    color: var(--primary-color);
    text-decoration: none;
}

.breadcrumb-item.active {
    color: var(--text-dark);
}

/* Page Title */
h1 {
    color: var(--text-dark);
    font-weight: 400;
    margin-bottom: 1.5rem;
}

/* Primary Button */
.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background-color: var(--primary-dark);
    border-color: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(139, 69, 67, 0.15);
}

/* Secondary Button */
.btn-secondary {
    background-color: var(--border-color);
    border-color: var(--border-color);
    color: var(--text-dark);
    transition: all 0.2s ease;
}

.btn-secondary:hover {
    background-color: darken(var(--border-color), 10%);
    border-color: darken(var(--border-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(196, 177, 177, 0.15);
}
</style>
