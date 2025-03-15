<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

try {
    // Validate required fields
    $required_fields = ['user_name', 'user_email', 'user_password', 'user_type', 'contact_number'];
    
    // Add cashier-specific required fields
    if ($_POST['user_type'] === 'Cashier') {
        $cashier_fields = [
            'branch_id',
            'employee_id',
            'date_hired',
            'emergency_contact',
            'emergency_number',
            'address'
        ];
        $required_fields = array_merge($required_fields, $cashier_fields);
    }

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Validate email format
    if (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_user WHERE user_email = ?");
    $stmt->execute([$_POST['user_email']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Email already exists");
    }

    // Start transaction
    $pdo->beginTransaction();

    // Insert into pos_user table
    $stmt = $pdo->prepare("
        INSERT INTO pos_user (
            user_name,
            user_email,
            user_password,
            user_type,
            contact_number,
            profile_image,
            user_status,
            created_at
        ) VALUES (
            :user_name,
            :user_email,
            :user_password,
            :user_type,
            :contact_number,
            :profile_image,
            'Active',
            NOW()
        )
    ");

    // Handle profile image upload
    $profile_image = '';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            throw new Exception("Invalid image format. Allowed formats: " . implode(', ', $allowed));
        }

        $upload_name = 'profile_' . time() . '.' . $ext;
        $upload_path = 'uploads/profiles/' . $upload_name;
        
        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
            throw new Exception("Failed to upload profile image");
        }
        
        $profile_image = $upload_path;
    }

    $stmt->execute([
        'user_name' => $_POST['user_name'],
        'user_email' => $_POST['user_email'],
        'user_password' => password_hash($_POST['user_password'], PASSWORD_DEFAULT),
        'user_type' => $_POST['user_type'],
        'contact_number' => $_POST['contact_number'],
        'profile_image' => $profile_image
    ]);

    $user_id = $pdo->lastInsertId();

    // If user is a cashier, insert additional information
    if ($_POST['user_type'] === 'Cashier') {
        $stmt = $pdo->prepare("
            INSERT INTO pos_cashier_details (
                user_id,
                branch_id,
                employee_id,
                date_hired,
                emergency_contact,
                emergency_number,
                address,
                notes,
                created_at
            ) VALUES (
                :user_id,
                :branch_id,
                :employee_id,
                :date_hired,
                :emergency_contact,
                :emergency_number,
                :address,
                :notes,
                NOW()
            )
        ");

        $stmt->execute([
            'user_id' => $user_id,
            'branch_id' => $_POST['branch_id'],
            'employee_id' => $_POST['employee_id'],
            'date_hired' => $_POST['date_hired'],
            'emergency_contact' => $_POST['emergency_contact'],
            'emergency_number' => $_POST['emergency_number'],
            'address' => $_POST['address'],
            'notes' => isset($_POST['notes']) ? $_POST['notes'] : null
        ]);
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'User added successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 