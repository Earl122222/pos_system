<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4" style="color: #8B4543; font-size: 1.25rem; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-weight: 500; background-color: #F8F9FA; padding: 1rem;">| Add New Branch</h1>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-store me-1"></i>
                    Branch Information
                </div>
                <div class="card-body">
                    <form id="addBranchForm" method="POST" action="process_add_branch.php">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="branch_name" class="form-label">Branch Name*</label>
                                    <input type="text" class="form-control" id="branch_name" name="branch_name" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="branch_code" class="form-label">Branch Code*</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="branch_code" name="branch_code" required>
                                        <button type="button" class="btn btn-secondary" onclick="generateBranchCode()">
                                            <i class="fas fa-sync-alt"></i> Generate
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="contact_number" class="form-label">Contact Number*</label>
                                    <input type="tel" class="form-control" id="contact_number" name="contact_number" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">Email Address*</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="street_address" class="form-label">Street Address*</label>
                                    <input type="text" class="form-control" id="street_address" name="street_address" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="barangay" class="form-label">Barangay*</label>
                                    <input type="text" class="form-control" id="barangay" name="barangay" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="city" class="form-label">City*</label>
                                    <input type="text" class="form-control" id="city" name="city" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="province" class="form-label">Province*</label>
                                    <input type="text" class="form-control" id="province" name="province" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="opening_date" class="form-label">Opening Date*</label>
                                    <input type="date" class="form-control" id="opening_date" name="opening_date" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="operating_hours" class="form-label">Operating Hours*</label>
                                    <div class="input-group">
                                        <input type="time" class="form-control" id="opening_time" name="opening_time" required>
                                        <span class="input-group-text">to</span>
                                        <input type="time" class="form-control" id="closing_time" name="closing_time" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="notes" class="form-label">Additional Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Branch
                            </button>
                            <a href="branch_details.php" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Card Styling */
.card {
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(139, 69, 67, 0.15);
}

.card-header {
    background: #8B4543;
    color: #ffffff;
    padding: 1rem 1.25rem;
    border-radius: 0.75rem 0.75rem 0 0;
    font-weight: 500;
}

.card-body {
    padding: 1.5rem;
}

/* Form Styling */
.form-label {
    font-weight: 500;
    color: #566a7f;
    margin-bottom: 0.5rem;
}

.form-control {
    padding: 0.5rem 1rem;
    font-size: 0.9375rem;
    border-radius: 0.5rem;
    border: 1px solid #d9dee3;
}

.form-control:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.25rem rgba(139, 69, 67, 0.25);
}

.input-group-text {
    background-color: #f5f5f5;
    border: 1px solid #d9dee3;
}

/* Button Styling */
.btn-primary {
    background-color: #8B4543;
    border-color: #8B4543;
}

.btn-primary:hover {
    background-color: #723937;
    border-color: #723937;
}

.btn-secondary {
    background-color: #8592a3;
    border-color: #8592a3;
}

.btn-secondary:hover {
    background-color: #6d788d;
    border-color: #6d788d;
}
</style>

<script>
function generateBranchCode() {
    // Generate a random 6-character alphanumeric code
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = 'BR-';
    for (let i = 0; i < 6; i++) {
        code += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    document.getElementById('branch_code').value = code;
}

$(document).ready(function() {
    // Form validation
    $('#addBranchForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
        submitBtn.prop('disabled', true);
        
        // Serialize form data
        const formData = $(this).serialize();
        
        // Submit form via AJAX
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('Branch added successfully!');
                    window.location.href = 'branch_details.php';
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while saving the branch.');
            },
            complete: function() {
                // Restore button state
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            }
        });
    });

    // Set default opening date to today
    document.getElementById('opening_date').valueAsDate = new Date();
});
</script>

<?php include('footer.php'); ?> 