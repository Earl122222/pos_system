<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$confData = getConfigData($pdo);

// Fetch categories for the dropdown
$stmt = $pdo->query("SELECT category_id, category_name FROM pos_category WHERE status = 'active' ORDER BY category_name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>

<div class="container-fluid px-4">
    <div class="d-flex align-items-center mt-4 mb-4">
        <div style="width: 4px; height: 24px; background-color: #8B4543; margin-right: 12px;"></div>
        <h1 style="color: #8B4543; font-size: 24px; font-weight: normal; margin: 0;">Product Management</h1>
    </div>

    <div class="card mb-4" style="background-color: #8B4543; border: none; border-radius: 8px;">
        <div class="card-body d-flex justify-content-between align-items-center py-2 px-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-utensils me-2 text-white"></i>
                <span class="text-white">Product List</span>
            </div>
            <button type="button" class="btn btn-success d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="fas fa-plus"></i>
                <span>Add Product</span>
            </button>
        </div>
    </div>

    <div class="card border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="productTable" class="table table-hover mb-0">
            <thead>
                <tr>
                            <th style="color: #8B4543;">ID</th>
                            <th style="color: #8B4543;">CATEGORY</th>
                            <th style="color: #8B4543;">PRODUCT NAME</th>
                            <th style="color: #8B4543;">PRICE</th>
                            <th style="color: #8B4543;">DESCRIPTION</th>
                            <th style="color: #8B4543;">INGREDIENTS</th>
                            <th style="color: #8B4543;">STATUS</th>
                            <th style="color: #8B4543;">IMAGE</th>
                            <th style="color: #8B4543;">ACTION</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form id="addProductForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category['category_id']) ?>">
                                    <?= htmlspecialchars($category['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="product_name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="product_name" name="product_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="product_price" name="product_price" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_status" class="form-label">Status</label>
                        <select class="form-select" id="product_status" name="product_status" required>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="ingredients" class="form-label">Ingredients</label>
                        <textarea class="form-control" id="ingredients" name="ingredients" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="product_image" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*">
                    </div>
                </form>
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveProduct">Add Product</button>
                </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editProductForm" enctype="multipart/form-data">
                    <input type="hidden" id="edit_product_id" name="product_id">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_category_id" class="form-label">Category</label>
                            <select class="form-select" id="edit_category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['category_id']) ?>">
                                        <?= htmlspecialchars($category['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_product_name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="edit_product_name" name="product_name" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_product_price" class="form-label">Price</label>
                            <input type="number" class="form-control" id="edit_product_price" name="product_price" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_product_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_product_status" name="product_status" required>
                                <option value="Available">Available</option>
                                <option value="Unavailable">Unavailable</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_ingredients" class="form-label">Ingredients</label>
                        <textarea class="form-control" id="edit_ingredients" name="ingredients" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_product_image" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="edit_product_image" name="product_image" accept="image/*">
                        <div id="currentImage" class="mt-2"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateProduct">Update Product</button>
            </div>
        </div>
    </div>
</div>

<!-- View Product Modal -->
<div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #8B4543; color: white;">
                <h5 class="modal-title" id="viewProductModalLabel" style="font-weight: 600;"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <div class="product-image-container" style="width: 100%; height: 200px; overflow: hidden; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                            <img id="viewProductImage" src="" alt="Product Image" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="product-details">
                            <div class="mb-3">
                                <label class="text-muted mb-1"><i class="fas fa-tag me-2"></i>Category</label>
                                <div id="viewCategory" class="fw-bold" style="color: #8B4543;"></div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted mb-1"><i class="fas fa-peso-sign me-2"></i>Price</label>
                                <div id="viewPrice" class="fw-bold" style="color: #8B4543; font-size: 1.2em;"></div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted mb-1"><i class="fas fa-circle-info me-2"></i>Status</label>
                                <div>
                                    <span id="viewStatus" class="badge" style="font-size: 0.9em; padding: 6px 12px; border-radius: 20px;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="text-muted mb-1"><i class="fas fa-align-left me-2"></i>Description</label>
                            <div id="viewDescription" class="p-3" style="background-color: #f8f9fa; border-radius: 8px; min-height: 60px;"></div>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted mb-1"><i class="fas fa-mortar-pestle me-2"></i>Ingredients</label>
                            <div id="viewIngredients" class="p-3" style="background-color: #f8f9fa; border-radius: 8px; min-height: 60px;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background-color: #f8f5f5;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
/* General Styles */
body {
    background-color: #f8f5f5;
}

.container-fluid {
    padding-top: 1.5rem;
    padding-bottom: 2rem;
}

/* Card Styles */
.card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.1);
}

/* Table Styles */
.table {
    margin-bottom: 0;
    background-color: #fff;
}

.table thead th {
    font-size: 13px;
    font-weight: 600;
    border: none;
    padding: 1.2rem 1rem;
    white-space: nowrap;
    background-color: #f9f2f2;
    color: #8B4543 !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table tbody tr {
    border-bottom: 1px solid #f0e6e6;
    transition: background-color 0.2s ease;
}

.table tbody tr:hover {
    background-color: #fdf8f8;
}

.table td {
    padding: 1.2rem 1rem;
    vertical-align: middle;
    color: #555;
    font-size: 14px;
}

/* Button Styles */
.btn-success {
    background-color: #4B7F52;
    border: none;
    padding: 0.6rem 1.2rem;
    font-size: 14px;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background-color: #3d6642;
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(75, 127, 82, 0.2);
}

/* Action Buttons */
.btn-view, .btn-edit, .btn-delete {
    width: 36px;
    height: 36px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    margin: 0 3px;
    border: none;
    color: white;
    transition: all 0.2s ease;
}

.btn-view { 
    background-color: #4B7F52; 
}
.btn-view:hover {
    background-color: #3d6642;
}

.btn-edit { 
    background-color: #8B4543; 
}
.btn-edit:hover {
    background-color: #723836;
}

.btn-delete { 
    background-color: #dc3545; 
}
.btn-delete:hover {
    background-color: #bb2d3b;
}

/* Status Badge */
.badge-active {
    background-color: #4B7F52;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 500;
    font-size: 13px;
    letter-spacing: 0.3px;
}

/* Modal Styles */
.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.modal-header {
    background-color: #8B4543;
    color: white;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    padding: 1.2rem;
}

.modal-title {
    font-weight: 500;
    color: white;
}

.btn-close {
    color: white;
    opacity: 1;
}

.modal-body {
    padding: 1.5rem;
}

.form-label {
    color: #8B4543;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border-radius: 6px;
    border: 1px solid #ddd;
    padding: 0.6rem 1rem;
}

.form-control:focus, .form-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
}

/* DataTable Customization */
.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 0.5rem;
    font-size: 14px;
}

.dataTables_wrapper .dataTables_filter input {
    width: 250px;
    margin-left: 0.5rem;
    background-color: #fff;
}

.dataTables_wrapper .dataTables_filter input:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    outline: none;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.5rem 1rem;
    margin: 0 3px;
    border: 1px solid #ddd;
    border-radius: 6px;
    color: #8B4543 !important;
    background: white !important;
    transition: all 0.2s ease;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #f9f2f2 !important;
    border-color: #8B4543;
    color: #8B4543 !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #8B4543 !important;
    color: white !important;
    border-color: #8B4543;
    font-weight: 500;
}

/* Product Image */
.product-image {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Modal Footer */
.modal-footer {
    border-top: 1px solid #eee;
    padding: 1.2rem;
}

.modal-footer .btn {
    padding: 0.6rem 1.5rem;
    border-radius: 6px;
    font-weight: 500;
}

.modal-footer .btn-secondary {
    background-color: #6c757d;
    border: none;
}

.modal-footer .btn-primary {
    background-color: #8B4543;
    border: none;
}

.modal-footer .btn-primary:hover {
    background-color: #723836;
}

/* Sweet Alert Customization */
.swal2-popup {
    border-radius: 12px;
}

.swal2-title {
    color: #8B4543;
}

.swal2-confirm {
    background-color: #8B4543 !important;
}

.swal2-cancel {
    background-color: #6c757d !important;
}

.product-details label {
    font-size: 0.9em;
    color: #666;
}

.product-details .badge {
    font-weight: 500;
}

#viewStatus.badge[data-status="Available"] {
    background-color: #4B7F52;
    color: white;
}

#viewStatus.badge[data-status="Unavailable"] {
    background-color: #dc3545;
    color: white;
}

#viewProductModal .modal-content {
    border: none;
    border-radius: 12px;
    overflow: hidden;
}

#viewProductModal .modal-header {
    border-bottom: none;
    padding: 1.5rem;
}

#viewProductModal .modal-body {
    padding: 1.5rem;
}

#viewProductModal .modal-footer {
    border-top: none;
}

#viewProductModal .btn-secondary {
    background-color: #6c757d;
    border: none;
    padding: 8px 20px;
    border-radius: 6px;
}

#viewProductModal .btn-secondary:hover {
    background-color: #5a6268;
}

#viewDescription:empty:before,
#viewIngredients:empty:before {
    content: "Not provided";
    color: #6c757d;
    font-style: italic;
}
</style>

<script>
$(document).ready(function() {
    var productTable = $('#productTable').DataTable({
        ajax: 'product_ajax.php',
        processing: true,
        serverSide: true,
        columns: [
            { data: 'product_id' },
            { data: 'category_name' },
            { data: 'product_name' },
            { 
                data: 'product_price',
                render: function(data) {
                    return '₱' + parseFloat(data).toFixed(2);
                }
            },
            { data: 'description' },
            { data: 'ingredients' },
            { 
                data: 'product_status',
                render: function(data) {
                    return '<span class="badge badge-active">' + data + '</span>';
                }
            },
            { 
                data: 'product_image',
                render: function(data) {
                    return data ? '<img src="' + data + '" class="product-image">' : 'No Image';
                }
            },
            {
                data: null,
                render: function(data) {
                    return `
                        <div class="d-flex gap-1">
                            <button class="btn btn-view view-btn" data-id="${data.product_id}" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-edit edit-btn" data-id="${data.product_id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-delete delete-btn" data-id="${data.product_id}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        pageLength: 10,
        language: {
            search: "",
            searchPlaceholder: "Search...",
            lengthMenu: "Show _MENU_ entries"
        },
        dom: '<"top d-flex justify-content-between align-items-center"lf>rt<"bottom d-flex justify-content-between align-items-center"ip><"clear">',
        ordering: true,
        responsive: true
    });

    // Handle form submission
    $('#saveProduct').click(function() {
        var formData = new FormData($('#addProductForm')[0]);
        
        $.ajax({
            url: 'process_add_product.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Properly hide modal and remove backdrop
                    $('#addProductModal').modal('hide');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                    
                    $('#addProductForm')[0].reset();
                    productTable.ajax.reload();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while processing your request.'
                });
            }
        });
    });

    // Edit Product
    $('#productTable').on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: 'get_product.php',
            type: 'GET',
            data: { id: id },
            success: function(response) {
                if (response.success) {
                    var product = response.data;
                    $('#edit_product_id').val(product.product_id);
                    $('#edit_category_id').val(product.category_id);
                    $('#edit_product_name').val(product.product_name);
                    $('#edit_product_price').val(product.product_price);
                    $('#edit_description').val(product.description);
                    $('#edit_ingredients').val(product.ingredients);
                    $('#edit_product_status').val(product.product_status);
                    
                    if (product.product_image) {
                        $('#currentImage').html(`<img src="${product.product_image}" alt="Current Image" style="max-height: 100px;">`);
                    } else {
                        $('#currentImage').html('No current image');
                    }
                    
                    $('#editProductModal').modal('show');
                }
            }
        });
    });

    // Update Product
    $('#updateProduct').click(function() {
        var formData = new FormData($('#editProductForm')[0]);
        
        $.ajax({
            url: 'update_product.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Properly hide modal and remove backdrop
                    $('#editProductModal').modal('hide');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                    
                    productTable.ajax.reload();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            }
        });
    });

    // Add modal hidden event handlers
    $('#addProductModal').on('hidden.bs.modal', function () {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });

    $('#editProductModal').on('hidden.bs.modal', function () {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });

    // Delete Product
    $('#productTable').on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This product will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'delete_product.php',
                    type: 'POST',
                    data: { product_id: id },
                    success: function(response) {
                        if (response.success) {
                            productTable.ajax.reload();
                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    }
                });
            }
        });
    });

    // View Product
    $('#productTable').on('click', '.view-btn', function() {
        var id = $(this).data('id');
        
        viewProduct(id);
    });
});

// Update the view product function
function viewProduct(id) {
    $.get('get_product.php', { id: id }, function(response) {
        if (response.success) {
            const product = response.data;
            
            // Update modal content
            $('#viewProductModalLabel').text(product.product_name);
            $('#viewCategory').text(product.category_name);
            $('#viewPrice').text('₱' + parseFloat(product.product_price).toFixed(2));
            $('#viewDescription').text(product.description || '');
            $('#viewIngredients').text(product.ingredients || '');
            
            // Update status with data attribute for styling
            $('#viewStatus')
                .text(product.product_status)
                .attr('data-status', product.product_status);
            
            // Update image with fallback
            const imgPath = product.product_image || 'uploads/products/default.jpg';
            $('#viewProductImage').attr('src', imgPath);
            
            // Show modal
            $('#viewProductModal').modal('show');
        } else {
            Swal.fire('Error', response.message, 'error');
        }
    }).fail(function() {
        Swal.fire('Error', 'Failed to fetch product details', 'error');
    });
}
</script>

<?php include('footer.php'); ?>
