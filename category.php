<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

include('header.php');
?>

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

/* Action Buttons */
.btn-group .btn, .btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.35rem;
    margin: 0 0.125rem;
    border: none;
    transition: all 0.2s ease;
}

.btn-warning {
    background: var(--warning-color);
    color: white;
}

.btn-warning:hover {
    background: darken(var(--warning-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(196, 128, 77, 0.15);
}

.btn-danger {
    background: var(--danger-color);
    color: white;
}

.btn-danger:hover {
    background: darken(var(--danger-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(179, 58, 58, 0.15);
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

/* Custom Modal Styles */
.modal-content {
    border-radius: 1rem;
}

.modal-header {
    border-radius: 1rem 1rem 0 0;
}

.bg-maroon {
    background-color: #8B4543;
}

.btn-maroon {
    background-color: #8B4543;
    color: white;
}

.btn-maroon:hover {
    background-color: #723937;
    color: white;
}

/* Form Control Styles */
.form-control, .form-select {
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    background-color: white;
    transition: all 0.2s ease-in-out;
}

.form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 0.25rem rgba(139, 69, 67, 0.25);
    border-color: rgba(139, 69, 67, 0.5);
}

/* Button Styles */
.btn-lg {
    border-radius: 0.75rem;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
}

.btn-light {
    background-color: #f8f9fa;
    border: none;
}

.btn-light:hover {
    background-color: #e9ecef;
}

/* Modal Animation */
.modal.fade .modal-dialog {
    transform: scale(0.95);
    transition: transform 0.2s ease-out;
}

.modal.show .modal-dialog {
    transform: scale(1);
}

/* SweetAlert2 Custom Styles */
.swal2-popup {
    border-radius: 1rem;
    padding: 2rem;
}

.swal2-title {
    color: var(--text-dark);
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.swal2-html-container {
    color: #6c757d;
    font-size: 1rem;
    margin: 1rem 0;
}

.swal2-icon {
    border-color: var(--warning-color);
    color: var(--warning-color);
}

.swal2-confirm {
    background-color: var(--danger-color) !important;
    border-radius: 0.75rem !important;
    padding: 0.75rem 1.5rem !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(179, 58, 58, 0.15) !important;
}

.swal2-confirm:focus {
    box-shadow: 0 0 0 0.25rem rgba(179, 58, 58, 0.25) !important;
}

.swal2-cancel {
    background-color: #f8f9fa !important;
    color: var(--text-dark) !important;
    border-radius: 0.75rem !important;
    padding: 0.75rem 1.5rem !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    margin-right: 0.5rem !important;
}

.swal2-cancel:hover {
    background-color: #e9ecef !important;
}

/* Modal Styling */
.modal-content {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
    border-radius: 1rem 1rem 0 0;
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    padding: 1.5rem;
    border: none;
    color: white;
}

.modal-header .btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.75;
}

.modal-body {
    padding: 2rem;
    background-color: #fff;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    background-color: #f8f9fa;
    border-radius: 0 0 1rem 1rem;
}

.form-label {
    font-weight: 500;
    color: #566a7f;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    border-radius: 0.5rem;
    border: 1px solid #d9dee3;
    transition: all 0.2s ease-in-out;
}

.form-control:focus, .form-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.25rem rgba(139, 69, 67, 0.25);
}

.btn {
    padding: 0.6875rem 1.5rem;
    font-size: 0.9375rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease-in-out;
}

.btn-primary {
    background-color: #8B4543;
    border-color: #8B4543;
}

.btn-primary:hover {
    background-color: #723937;
    border-color: #723937;
}

.btn-secondary {
    color: #566a7f;
    background-color: #f8f9fa;
    border-color: #d9dee3;
}

.btn-secondary:hover {
    background-color: #e9ecef;
    border-color: #d9dee3;
    color: #566a7f;
}
</style>

<div class="container-fluid px-4">
    <h1 class="mt-4" style="color: #8B4543; font-size: 1.25rem; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-weight: 500; background-color: #F8F9FA; padding: 1rem;">| Category Management</h1>
        <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-list me-1"></i>
                        Category List
            </div>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fas fa-plus me-1"></i> Add Category
                    </button>
    </div>
    <div class="card-body">
                    <table id="categoryTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                                <th width="5%">ID</th>
                                <th width="20%">Name</th>
                                <th width="45%">Description</th>
                                <th width="15%">Status</th>
                                <th width="15%">Action</th>
                </tr>
            </thead>
        </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <!-- Modal Header -->
            <div class="modal-header bg-maroon text-white py-3">
                <h5 class="modal-title" id="addCategoryModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add Category
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body bg-light p-4">
                <form id="addCategoryForm">
                    <div class="mb-4">
                        <label for="category_name" class="form-label fw-medium">Category Name</label>
                        <input type="text" class="form-control form-control-lg border-0 shadow-sm" id="category_name" name="category_name" required>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-medium">Description</label>
                        <textarea class="form-control form-control-lg border-0 shadow-sm" id="description" name="description" rows="3" placeholder="Enter category description"></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="category_status" class="form-label fw-medium">Status</label>
                        <select class="form-select form-select-lg border-0 shadow-sm" id="category_status" name="category_status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 px-4 pb-4 pt-0">
                <button type="button" class="btn btn-light btn-lg px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-maroon btn-lg px-4" id="saveCategory">
                    <i class="fas fa-save me-2"></i>Save Category
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCategoryForm">
                    <input type="hidden" id="edit_category_id" name="category_id">
                    <div class="mb-3">
                        <label for="edit_category_name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" placeholder="Enter category description (optional)"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_category_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_category_status" name="category_status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveEditButton">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </div>
        </div>
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
                "data": "description",
                "render": function(data, type, row) {
                    return data ? data : '<em class="text-muted">No description</em>';
                }
            },
            { 
                "data": null,
                "render": function(data, type, row) {
                    if(row.status === 'active'){
                        return '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>';
                    } else {
                        return '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Inactive</span>';
                    }
                }
            },
            {
                "data": null,
                "render": function(data, type, row) {
                    return `
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning btn-sm edit-btn" data-id="${row.category_id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="${row.category_id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>`;
                }
            }
        ],
        "order": [[0, "desc"]],
        "pageLength": 10,
        "responsive": true,
        "language": {
            "emptyTable": "No categories found",
            "info": "Showing _START_ to _END_ of _TOTAL_ categories",
            "infoEmpty": "Showing 0 to 0 of 0 categories",
            "infoFiltered": "(filtered from _MAX_ total categories)"
        }
    });

    // Handle Save Category Button Click
    $('#saveCategory').click(function() {
        let formData = {
            category_name: $('#category_name').val(),
            description: $('#description').val(),
            category_status: $('#category_status').val()
        };

        $.ajax({
            url: 'add_category.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Close modal properly
                    const modal = bootstrap.Modal.getInstance($('#addCategoryModal'));
                    modal.hide();
                    
                    // Remove modal backdrop manually if it persists
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    
                    // Clear form
                    $('#addCategoryForm')[0].reset();
                    
                    // Reload table
                    $('#categoryTable').DataTable().ajax.reload();
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Category added successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Error adding category'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Error occurred while adding category'
                });
            }
        });
    });

    // Handle modal hidden event
    $('#addCategoryModal').on('hidden.bs.modal', function () {
        // Clear form when modal is closed
        $('#addCategoryForm')[0].reset();
        // Remove any remaining backdrop
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });

    // Handle Delete Button Click
    $(document).on('click', '.delete-btn', function() {
        let categoryId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#B33A3A',
            cancelButtonColor: '#f8f9fa',
            confirmButtonText: '<i class="fas fa-trash me-2"></i>Yes, delete it!',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
            customClass: {
                confirmButton: 'btn btn-danger btn-lg',
                cancelButton: 'btn btn-light btn-lg'
            },
            buttonsStyling: false,
            padding: '2rem',
            width: 400,
            showClass: {
                popup: 'animate__animated animate__fadeInDown animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp animate__faster'
            }
        }).then((result) => {
            if (result.isConfirmed) {
            $.ajax({
                url: 'delete_category.php',
                type: 'POST',
                data: { id: categoryId },
                success: function(response) {
                        if (response.success) {
                            $('#categoryTable').DataTable().ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Category has been deleted successfully.',
                                timer: 2000,
                                showConfirmButton: false,
                                customClass: {
                                    popup: 'animate__animated animate__fadeInDown animate__faster'
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message || 'Failed to delete category.',
                                customClass: {
                                    popup: 'animate__animated animate__fadeInDown animate__faster'
                                }
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while deleting the category.',
                            customClass: {
                                popup: 'animate__animated animate__fadeInDown animate__faster'
                            }
                        });
                    }
                });
            }
        });
    });

    // Handle Edit Button Click
    $(document).on('click', '.edit-btn', function() {
        let categoryId = $(this).data('id');
        
        // Fetch category data
        $.ajax({
            url: 'edit_category.php',
            type: 'GET',
            data: { id: categoryId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let category = response.data;
                    $('#edit_category_id').val(category.category_id);
                    $('#edit_category_name').val(category.category_name);
                    $('#edit_description').val(category.description);
                    $('#edit_category_status').val(category.status);
                    
                    // Show the modal
                    $('#editCategoryModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to fetch category details',
                        customClass: {
                            popup: 'animate__animated animate__fadeInDown animate__faster'
                        }
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while fetching category details',
                    customClass: {
                        popup: 'animate__animated animate__fadeInDown animate__faster'
                    }
                });
            }
        });
    });

    // Handle Save Edit Button Click
    $('#saveEditButton').click(function() {
        let form = $('#editCategoryForm');
        let formData = form.serialize();
        
        // Show confirmation dialog
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to save these changes?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#8B4543',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-save me-2"></i>Yes, save it!',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
            customClass: {
                confirmButton: 'btn btn-primary btn-lg',
                cancelButton: 'btn btn-secondary btn-lg'
            },
            buttonsStyling: false,
            padding: '2rem',
            showClass: {
                popup: 'animate__animated animate__fadeInDown animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp animate__faster'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'edit_category.php',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#editCategoryModal').modal('hide');
                    $('#categoryTable').DataTable().ajax.reload();
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: 'Category has been updated successfully.',
                                timer: 2000,
                                showConfirmButton: false,
                                customClass: {
                                    popup: 'animate__animated animate__fadeInDown animate__faster'
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message,
                                customClass: {
                                    popup: 'animate__animated animate__fadeInDown animate__faster'
                                }
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while updating the category.',
                            customClass: {
                                popup: 'animate__animated animate__fadeInDown animate__faster'
                            }
                        });
                }
            });
        }
        });
    });

    // Reset form when modal is closed
    $('#editCategoryModal').on('hidden.bs.modal', function() {
        $('#editCategoryForm')[0].reset();
    });
});
</script>
