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
</style>

<div class="container-fluid px-4">
    <h1 class="mt-4" style="color: #8B4543; font-size: 1.25rem; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-weight: 500; background-color: #F8F9FA; padding: 1rem;">| User Management</h1>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-users me-1"></i>
                        User List
                    </div>
                    <a href="add_user.php" class="btn btn-success">
                        <i class="fas fa-user-plus me-1"></i> Add User
                    </a>
                </div>
                <div class="card-body">
                    <table id="userTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userDetailsModalLabel">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <img id="userProfileImage" src="" alt="Profile Image" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <span id="userName"></span></p>
                        <p><strong>Email:</strong> <span id="userEmail"></span></p>
                        <p><strong>Type:</strong> <span id="userType"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Contact:</strong> <span id="userContact"></span></p>
                        <p><strong>Status:</strong> <span id="userStatus"></span></p>
                        <p><strong>Created At:</strong> <span id="userCreatedAt"></span></p>
                    </div>
                </div>
                <!-- Additional Cashier Details Section -->
                <div id="cashierDetails" style="display: none;">
                    <hr>
                    <h6 class="mb-3">Cashier Details</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Employee ID:</strong> <span id="employeeId"></span></p>
                            <p><strong>Branch:</strong> <span id="branchName"></span></p>
                            <p><strong>Date Hired:</strong> <span id="dateHired"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Emergency Contact:</strong> <span id="emergencyContact"></span></p>
                            <p><strong>Emergency Number:</strong> <span id="emergencyNumber"></span></p>
                            <p><strong>Address:</strong> <span id="address"></span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>

<script>
$(document).ready(function() {
    $('#userTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "user_ajax.php",
            "type": "GET"
        },
        "columns": [
            { "data": "user_id" },
            { "data": "user_name" },
            { "data": "user_email" },
            { "data": "user_type" },
            { 
                "data": null,
                "render": function(data, type, row){
                    if(row.user_status === 'Active'){
                        return `<span class="badge bg-success">Active</span>`;
                    } else {
                        return `<span class="badge bg-danger">Inactive</span>`;
                    }
                }
            },
            {
                "data": null,
                "render": function(data, type, row){
                    return `
                    <div class="text-center">
                        <button class="btn btn-info btn-sm view-details" data-id="${row.user_id}">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <a href="edit_user.php?id=${row.user_id}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button class="btn btn-danger btn-sm delete-btn" data-id="${row.user_id}">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>`;
                }
            }
        ]
    });

    // Handle View Details Button Click
    $(document).on('click', '.view-details', function() {
        let userId = $(this).data('id');
        $.ajax({
            url: 'get_user_details.php',
            type: 'GET',
            data: { id: userId },
            success: function(response) {
                if (response.success) {
                    const user = response.data;
                    
                    // Set profile image
                    $('#userProfileImage').attr('src', user.profile_image ? user.profile_image : 'uploads/profiles/default.png');
                    
                    // Set user details
                    $('#userName').text(user.user_name);
                    $('#userEmail').text(user.user_email);
                    $('#userType').text(user.user_type);
                    $('#userContact').text(user.contact_number || 'N/A');
                    $('#userStatus').html(user.user_status === 'Active' ? 
                        '<span class="badge bg-success">Active</span>' : 
                        '<span class="badge bg-danger">Inactive</span>');
                    $('#userCreatedAt').text(user.created_at);

                    // Handle cashier details
                    if (user.user_type === 'Cashier' && user.cashier_details) {
                        $('#cashierDetails').show();
                        $('#employeeId').text(user.cashier_details.employee_id);
                        $('#branchName').text(user.cashier_details.branch_name);
                        $('#dateHired').text(user.cashier_details.date_hired);
                        $('#emergencyContact').text(user.cashier_details.emergency_contact);
                        $('#emergencyNumber').text(user.cashier_details.emergency_number);
                        $('#address').text(user.cashier_details.address);
                    } else {
                        $('#cashierDetails').hide();
                    }

                    // Show modal
                    $('#userDetailsModal').modal('show');
                } else {
                    alert('Failed to load user details');
                }
            },
            error: function() {
                alert('Error loading user details');
            }
        });
    });

    // Handle Delete Button Click
    $(document).on('click', '.delete-btn', function() {
        let userId = $(this).data('id');
        if (confirm("Are you sure you want to delete this user?")) {
            $.ajax({
                url: 'delete_user.php',
                type: 'POST',
                data: { id: userId },
                success: function(response) {
                    alert(response);
                    $('#userTable').DataTable().ajax.reload();
                }
            });
        }
    });
});
</script>
