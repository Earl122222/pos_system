<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$confData = getConfigData($pdo);

// Fetch categories for the dropdown
$stmt = $pdo->query("SELECT * FROM pos_category WHERE status = 'active'");
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" style="color: #8B4543;">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addProductForm" enctype="multipart/form-data">
                <div class="modal-body px-4">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select shadow-none" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control shadow-none" name="product_name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control shadow-none" name="product_price" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control shadow-none" name="description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ingredients</label>
                        <textarea class="form-control shadow-none" name="ingredients" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" class="form-control shadow-none" name="product_image" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select shadow-none" name="product_status" required>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* General Styles */
body {
    background-color: #ffffff;
}

.container-fluid {
    padding-top: 1rem;
    padding-bottom: 1.5rem;
}

/* Card Styles */
.card {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

/* Table Styles */
.table {
    margin-bottom: 0;
}

.table thead th {
    font-size: 13px;
    font-weight: 500;
    border: none;
    padding: 1rem;
    white-space: nowrap;
    background-color: #ffffff;
}

.table tbody tr {
    border-bottom: 1px solid #f0f0f0;
}

.table td {
    padding: 1rem;
    vertical-align: middle;
    color: #555;
    font-size: 14px;
}

/* Button Styles */
.btn-success {
    background-color: #4B7F52;
    border: none;
    padding: 0.5rem 1rem;
    font-size: 14px;
    border-radius: 4px;
}

.btn-success:hover {
    background-color: #3d6642;
}

/* Action Buttons */
.btn-view, .btn-edit, .btn-delete {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    margin: 0 2px;
    border: none;
    color: white;
}

.btn-view { background-color: #17a2b8; }
.btn-edit { background-color: #8B4543; }
.btn-delete { background-color: #dc3545; }

/* Status Badge */
.badge-active {
    background-color: #4B7F52;
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 4px;
    font-weight: normal;
    font-size: 13px;
}

/* DataTable Customization */
.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 0.4rem;
    font-size: 14px;
}

.dataTables_wrapper .dataTables_filter input {
    width: 200px;
    margin-left: 0.5rem;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.4rem 0.8rem;
    margin: 0 2px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    color: #666 !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #8B4543 !important;
    color: white !important;
    border-color: #8B4543;
}

/* Product Image */
.product-image {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
}

/* Search and Length Menu */
.dataTables_wrapper .top {
    padding: 1rem;
    background-color: #ffffff;
    border-bottom: 1px solid #f0f0f0;
}

.dataTables_wrapper .bottom {
    padding: 1rem;
    background-color: #ffffff;
}

.dataTables_wrapper .dataTables_info {
    color: #666;
    font-size: 14px;
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
                            <button class="btn btn-view" onclick="viewProduct(${data.product_id})" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-edit" onclick="editProduct(${data.product_id})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-delete" onclick="deleteProduct(${data.product_id})" title="Delete">
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
    $('#addProductForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        
        $.ajax({
            url: 'process_add_product.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    
                    $('#addProductModal').modal('hide');
                    $('#addProductForm')[0].reset();
                    productTable.ajax.reload();
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
});

// Function to view product details
function viewProduct(productId) {
    $.ajax({
        url: 'get_product.php',
        type: 'GET',
        data: { id: productId },
        success: function(response) {
            if (response.success) {
                const product = response.data;
                Swal.fire({
                    title: product.product_name,
                    html: `
                        <div class="text-left">
                            ${product.product_image ? 
                                `<div class="mb-3 text-center">
                                    <img src="${product.product_image}" alt="${product.product_name}" style="max-width: 200px; border-radius: 8px;">
                                </div>` : ''
                            }
                            <p><strong>Category:</strong> ${product.category_name}</p>
                            <p><strong>Price:</strong> ₱${parseFloat(product.product_price).toFixed(2)}</p>
                            <p><strong>Description:</strong> ${product.description || '-'}</p>
                            <p><strong>Ingredients:</strong> ${product.ingredients || '-'}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge ${product.product_status === 'Available' ? 'bg-success' : 'bg-danger'}">
                                    ${product.product_status}
                                </span>
                            </p>
                        </div>
                    `,
                    customClass: {
                        container: 'product-view-modal'
                    }
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to load product details', 'error');
        }
    });
}

// Function to edit product
function editProduct(productId) {
    $.ajax({
        url: 'get_product.php',
        type: 'GET',
        data: { id: productId },
        success: function(response) {
            if (response.success) {
                const product = response.data;
                $('#editProductId').val(product.product_id);
                $('#editCategoryId').val(product.category_id);
                $('#editProductName').val(product.product_name);
                $('#editProductPrice').val(product.product_price);
                $('#editDescription').val(product.description);
                $('#editIngredients').val(product.ingredients);
                $('#editProductStatus').val(product.status);
                if (product.product_image) {
                    $('#currentProductImage').attr('src', product.product_image);
                    $('#currentProductImage').show();
                } else {
                    $('#currentProductImage').hide();
                }
                $('#editProductModal').modal('show');
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to load product details', 'error');
        }
    });
}

// Function to delete product
function deleteProduct(productId) {
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
                data: { product_id: productId },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success');
                        // Refresh the DataTable
                        $('#productTable').DataTable().ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to delete product', 'error');
                }
            });
        }
    });
}

// Handle edit product form submission
$('#editProductForm').on('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    $.ajax({
        url: 'update_product.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#editProductModal').modal('hide');
                Swal.fire('Success', response.message, 'success');
                $('#productTable').DataTable().ajax.reload();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to update product', 'error');
        }
    });
});

// Handle add product form submission
$('#addProductForm').on('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    $.ajax({
        url: 'process_add_product.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#addProductModal').modal('hide');
                Swal.fire('Success', response.message, 'success');
                $('#productTable').DataTable().ajax.reload();
                $('#addProductForm')[0].reset();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to add product', 'error');
        }
    });
});
</script>

<?php include('footer.php'); ?>
