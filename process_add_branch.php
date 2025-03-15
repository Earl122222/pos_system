<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    // Validate required fields
    $required_fields = [
        'branch_name',
        'branch_code',
        'contact_number',
        'email',
        'street_address',
        'barangay',
        'city',
        'province',
        'opening_date',
        'opening_time',
        'closing_time'
    ];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Check if branch code already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_branch WHERE branch_code = ?");
    $stmt->execute([$_POST['branch_code']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Branch code already exists");
    }

    // Format the complete address
    $complete_address = implode(', ', [
        $_POST['street_address'],
        $_POST['barangay'],
        $_POST['city'],
        $_POST['province']
    ]);

    // Format operating hours
    $operating_hours = $_POST['opening_time'] . ' - ' . $_POST['closing_time'];

    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO pos_branch (
            branch_name,
            branch_code,
            contact_number,
            email,
            street_address,
            barangay,
            city,
            province,
            complete_address,
            opening_date,
            operating_hours,
            notes,
            status,
            created_at
        ) VALUES (
            :branch_name,
            :branch_code,
            :contact_number,
            :email,
            :street_address,
            :barangay,
            :city,
            :province,
            :complete_address,
            :opening_date,
            :operating_hours,
            :notes,
            'Active',
            NOW()
        )
    ");

    $stmt->execute([
        'branch_name' => $_POST['branch_name'],
        'branch_code' => $_POST['branch_code'],
        'contact_number' => $_POST['contact_number'],
        'email' => $_POST['email'],
        'street_address' => $_POST['street_address'],
        'barangay' => $_POST['barangay'],
        'city' => $_POST['city'],
        'province' => $_POST['province'],
        'complete_address' => $complete_address,
        'opening_date' => $_POST['opening_date'],
        'operating_hours' => $operating_hours,
        'notes' => !empty($_POST['notes']) ? $_POST['notes'] : null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Branch added successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 