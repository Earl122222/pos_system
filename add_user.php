<?php 

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$role = isset($_GET['role']) ? $_GET['role'] : '';

// Always fetch active branches for the dropdown
$stmt = $pdo->query("SELECT branch_id, branch_name, branch_code FROM pos_branch WHERE status = 'Active'");
$branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4" style="color: #8B4543; font-size: 1.25rem; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-weight: 500; background-color: #F8F9FA; padding: 1rem;">
        | Add New <?php echo ucfirst($role); ?>
    </h1>

    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-plus me-1"></i>
                    <?php echo ucfirst($role); ?> Information
                </div>
                <div class="card-body">
                    <form id="addUserForm" method="POST" action="process_add_user.php" enctype="multipart/form-data">
                        <input type="hidden" name="user_type" value="<?php echo ucfirst($role); ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-4">Basic Information</h5>
                                
                                <div class="form-group mb-3">
                                    <label for="user_name" class="form-label">Full Name*</label>
                                    <input type="text" class="form-control" id="user_name" name="user_name" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="user_email" class="form-label">Email Address*</label>
                                    <input type="email" class="form-control" id="user_email" name="user_email" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="user_password" class="form-label">Password*</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="user_password" name="user_password" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="contact_number" class="form-label">Contact Number*</label>
                                    <input type="tel" class="form-control" id="contact_number" name="contact_number" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="profile_image" class="form-label">Profile Image</label>
                                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                </div>
                            </div>

                            <?php if ($role === 'cashier' || $role === 'stockman'): ?>
                            <div class="col-md-6">
                                <h5 class="mb-4">Work Information</h5>

                                <div class="form-group mb-3">
                                    <label for="branch_id" class="form-label">Assigned Branch*</label>
                                    <select class="form-select" id="branch_id" name="branch_id" required>
                                        <option value="">Select Branch</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['branch_id']; ?>">
                                                <?php echo $branch['branch_name'] . ' (' . $branch['branch_code'] . ')'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="employee_id" class="form-label">Employee ID*</label>
                                    <input type="text" class="form-control" id="employee_id" name="employee_id" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="date_hired" class="form-label">Date Hired*</label>
                                    <input type="date" class="form-control" id="date_hired" name="date_hired" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="emergency_contact" class="form-label">Emergency Contact*</label>
                                    <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="emergency_number" class="form-label">Emergency Contact Number*</label>
                                    <input type="tel" class="form-control" id="emergency_number" name="emergency_number" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <h5 class="mb-4 mt-3">Additional Information</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="address" class="form-label">Complete Address*</label>
                                            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="notes" class="form-label">Additional Notes</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save <?php echo ucfirst($role); ?>
                            </button>
                            <a href="user.php" class="btn btn-secondary">
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
/* Form Styling */
.form-label {
    font-weight: 500;
    color: #566a7f;
}

.form-control, .form-select {
    border-radius: 0.5rem;
    border: 1px solid #d9dee3;
    padding: 0.5rem 1rem;
    font-size: 0.9375rem;
}

.form-control:focus, .form-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.25rem rgba(139, 69, 67, 0.25);
}

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

/* Section Headers */
h5 {
    color: #566a7f;
    font-weight: 500;
    margin-bottom: 1.5rem;
}
</style>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('user_password');
    const icon = document.querySelector('.fa-eye');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

$(document).ready(function() {
    // Set default date hired to today
    document.getElementById('date_hired').valueAsDate = new Date();

    // Form validation
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('User added successfully!');
                    window.location.href = 'user.php';
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while saving the user.');
            }
        });
    });

    // Phone number formatting
    $('#contact_number, #emergency_number').on('input', function() {
        let number = $(this).val().replace(/\D/g, '');
        if (number.length > 10) {
            number = number.substring(0, 10);
        }
        $(this).val(number.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3'));
    });
});
</script>

<?php include('footer.php'); ?>