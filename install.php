<?php

session_start();

// Check if installation is already done
if (file_exists('config.php')) {
    header('Location: index.php');
    exit;
}

$errors = [];

$install_step = 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['db_config'])){
        $host = trim($_POST['db_host']);
        $db = trim($_POST['db_name']);
        $user = trim($_POST['db_user']);
        $pass = trim($_POST['db_pass']);

        if (empty($host)) {
            $errors[] = "Database host is required.";
        }
        if (empty($db)) {
            $errors[] = "Database name is required.";
        }
        if (empty($user)) {
            $errors[] = "Database user is required.";
        }
        if (empty($pass)) {
            $errors[] = "Database password is required.";
        }

        if (empty($errors)) {
            /*try {
                $pdo = new PDO("mysql:host=$host", $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Create database if it doesn't exist
                $pdo->exec("CREATE DATABASE IF NOT EXISTS $db");
                $pdo->exec("USE $db");

                // Create tables
                $tables = [
                    "CREATE TABLE IF NOT EXISTS pos_user (
                        user_id INT AUTO_INCREMENT PRIMARY KEY,
                        user_name VARCHAR(255) NOT NULL,
                        user_email VARCHAR(255) NOT NULL UNIQUE,
                        user_password VARCHAR(255) NOT NULL,
                        user_type ENUM('Admin', 'User') NOT NULL,
                        user_status ENUM('Active', 'Inactive') NOT NULL
                    )",
                    "CREATE TABLE IF NOT EXISTS pos_category (
                        category_id INT AUTO_INCREMENT PRIMARY KEY,
                        category_name VARCHAR(255) NOT NULL,
                        category_status ENUM('Active', 'Inactive') NOT NULL
                    )",
                    "CREATE TABLE IF NOT EXISTS pos_product (
                        product_id INT AUTO_INCREMENT PRIMARY KEY,
                        category_id INT,
                        product_name VARCHAR(255) NOT NULL,
                        product_image VARCHAR(100) NOT NULL,
                        product_price DECIMAL(10, 2) NOT NULL,
                        product_status ENUM('Available', 'Out of Stock') NOT NULL,
                        FOREIGN KEY (category_id) REFERENCES pos_category(category_id)
                    )",
                    "CREATE TABLE IF NOT EXISTS pos_order (
                        order_id INT AUTO_INCREMENT PRIMARY KEY,
                        order_number VARCHAR(255) UNIQUE NOT NULL,
                        order_total DECIMAL(10, 2) NOT NULL,
                        order_money_receive DECIMAL(10, 2) NOT NULL,
                        order_money_return DECIMAL(10, 2) NOT NULL,
                        order_datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        order_created_by INT,
                        FOREIGN KEY (order_created_by) REFERENCES pos_user(user_id)
                    )",
                    "CREATE TABLE IF NOT EXISTS pos_order_item (
                        order_item_id INT AUTO_INCREMENT PRIMARY KEY,
                        order_id INT,
                        product_id INT,
                        product_qty INT NOT NULL,
                        product_price DECIMAL(10, 2) NOT NULL,
                        FOREIGN KEY (order_id) REFERENCES pos_order(order_id),
                        FOREIGN KEY (product_id) REFERENCES pos_product(product_id)
                    )",
                    "CREATE TABLE IF NOT EXISTS pos_configuration (
                        config_id INT AUTO_INCREMENT PRIMARY KEY,
                        restaurant_name VARCHAR(255) NOT NULL,
                        restaurant_address VARCHAR(255) NOT NULL,
                        restaurant_phone VARCHAR(20) NOT NULL,
                        restaurant_email VARCHAR(255),
                        opening_hours VARCHAR(255),
                        closing_hours VARCHAR(255),
                        tax_rate DECIMAL(5, 2),
                        currency VARCHAR(10),
                        logo VARCHAR(100)
                    );"
                ];

                foreach ($tables as $table) {
                    $pdo->exec($table);
                }

                $install_step = 2;
            } catch (PDOException $e) {
                $errors[] = "DB ERROR: ". $e->getMessage();
            }*/
            $_SESSION['install_data']['host'] = $host;
            $_SESSION['install_data']['user'] = $user;
            $_SESSION['install_data']['pass'] = $pass;
            $_SESSION['install_data']['db'] = $db;
            $_SESSION['install_data']['table'] = [
                "CREATE TABLE IF NOT EXISTS pos_user (
                    user_id INT AUTO_INCREMENT PRIMARY KEY,
                    user_name VARCHAR(255) NOT NULL,
                    user_email VARCHAR(255) NOT NULL UNIQUE,
                    user_password VARCHAR(255) NOT NULL,
                    user_type ENUM('Admin', 'User') NOT NULL,
                    user_status ENUM('Active', 'Inactive') NOT NULL
                )",
                "CREATE TABLE IF NOT EXISTS pos_category (
                    category_id INT AUTO_INCREMENT PRIMARY KEY,
                    category_name VARCHAR(255) NOT NULL,
                    category_status ENUM('Active', 'Inactive') NOT NULL
                )",
                "CREATE TABLE IF NOT EXISTS pos_product (
                    product_id INT AUTO_INCREMENT PRIMARY KEY,
                    category_id INT,
                    product_name VARCHAR(255) NOT NULL,
                    product_image VARCHAR(100) NOT NULL,
                    product_price DECIMAL(10, 2) NOT NULL,
                    product_status ENUM('Available', 'Out of Stock') NOT NULL,
                    FOREIGN KEY (category_id) REFERENCES pos_category(category_id)
                )",
                "CREATE TABLE IF NOT EXISTS pos_order (
                    order_id INT AUTO_INCREMENT PRIMARY KEY,
                    order_number VARCHAR(255) UNIQUE NOT NULL,
                    order_total DECIMAL(10, 2) NOT NULL,
                    order_datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    order_created_by INT,
                    FOREIGN KEY (order_created_by) REFERENCES pos_user(user_id)
                )",
                "CREATE TABLE IF NOT EXISTS pos_order_item (
                    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id INT,
                    product_name varchar(100) NOT NULL,
                    product_qty INT NOT NULL,
                    product_price DECIMAL(10, 2) NOT NULL,
                    FOREIGN KEY (order_id) REFERENCES pos_order(order_id)
                )",
                "CREATE TABLE IF NOT EXISTS pos_configuration (
                    config_id INT AUTO_INCREMENT PRIMARY KEY,
                    restaurant_name VARCHAR(255) NOT NULL,
                    restaurant_address VARCHAR(255) NOT NULL,
                    restaurant_phone VARCHAR(20) NOT NULL,
                    restaurant_email VARCHAR(255),
                    opening_hours VARCHAR(255),
                    closing_hours VARCHAR(255),
                    tax_rate DECIMAL(5, 2),
                    currency VARCHAR(10),
                    logo VARCHAR(100)
                );"
            ];
            $_SESSION['step'] = 2;
        }
    }
    if(isset($_POST['admin_account'])){
        $user_name = trim($_POST['user_name']);
        $user_email = trim($_POST['user_email']);
        $user_password = trim($_POST['user_password']);

        if (empty($user_name)) {
            $errors[] = "Admin Name is required.";
        }

        if (empty($user_email)) {
            $errors[] = "Admin email is required.";
        } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }
    
        if (empty($user_password)) {
            $errors[] = "Admin password is required.";
        } elseif (strlen($user_password) < 6) {
            $errors[] = "Password must be at least 6 characters.";
        }

        if (empty($errors)) {
            $hashed_password = password_hash($user_password, PASSWORD_BCRYPT);
            $_SESSION['install_data']['user_password'] = $hashed_password;
            $_SESSION['install_data']['user_email'] = $user_email;
            $_SESSION['install_data']['user_name'] = $user_name;
            $_SESSION['step'] = 3;
        }
    }
    if(isset($_POST['restaurant_config'])){
        $restaurant_name = trim($_POST['restaurant_name']);
        $restaurant_address = trim($_POST['restaurant_address']);
        $restaurant_phone = trim($_POST['restaurant_phone']);
        $restaurant_email = trim($_POST['restaurant_email']);
        $opening_hours = trim($_POST['opening_hours']);
        $closing_hours = trim($_POST['closing_hours']);
        $tax_rate = trim($_POST['tax_rate']);
        $currency = trim($_POST['currency']);
        $logo_path = '';
        // File upload handling
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $allowed_ext = ['jpg', 'jpeg', 'png'];
            $file_ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            if (in_array(strtolower($file_ext), $allowed_ext)) {
                $logo_path = 'uploads/' . basename($_FILES['logo']['name']);
                if (!move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path)) {
                    $errors[] = 'Error uploading the logo.';
                }
            } else {
                $errors[] = 'Invalid file type for logo. Only JPG, JPEG, PNG, and GIF are allowed.';
            }
        } else {
            $logo_path = ''; // In case no logo is uploaded, set the path to null
        }
        if (empty($restaurant_name)) {
            $errors[] = "Restaurant Name is required.";
        }
        if (empty($restaurant_address)) {
            $errors[] = "Restaurant Address is required.";
        }
        if (empty($restaurant_phone)) {
            $errors[] = "Restaurant Phone Number is required.";
        }
        if (empty($restaurant_email)) {
            $errors[] = "Restaurant Email is required.";
        }
        elseif (!filter_var($restaurant_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid Restaurant email format.";
        }
        if (empty($opening_hours)) {
            $errors[] = "Restaurant Opening Hours is required.";
        }
        if (empty($closing_hours)) {
            $errors[] = "Restaurant Closing Hours is required.";
        }
        if (empty($tax_rate)) {
            $errors[] = "Restaurant Tax Rate is required.";
        }
        if (empty($currency)) {
            $errors[] = "Restaurant Currency is required.";
        }

        if (empty($errors)) {
            try {
                $pdo = new PDO("mysql:host=".$_SESSION['install_data']['host']."", $_SESSION['install_data']['user'], $_SESSION['install_data']['pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Create database if it doesn't exist
                $pdo->exec("CREATE DATABASE IF NOT EXISTS ".$_SESSION['install_data']['db']."");
                $pdo->exec("USE " . $_SESSION['install_data']['db'] . "");

                foreach ($_SESSION['install_data']['table'] as $table) {
                    $pdo->exec($table);
                }

                //Set Up Admin Account
                $stmt = $pdo->prepare("INSERT INTO pos_user (user_name, user_email, user_password, user_type, user_status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['install_data']['user_name'], $_SESSION['install_data']['user_email'], $_SESSION['install_data']['user_password'], 'Admin', 'Active']);

                // Create a config.php file to signal the installation completion
                $config_content = "<?php\n";
                $config_content .= "define('DB_HOST', '".$_SESSION['install_data']['host']."');\n";
                $config_content .= "define('DB_NAME', '".$_SESSION['install_data']['db']."');\n";
                $config_content .= "define('DB_USER', '".$_SESSION['install_data']['user']."');\n";
                $config_content .= "define('DB_PASS', '".$_SESSION['install_data']['pass']."');\n";
                file_put_contents('config.php', $config_content);

                //Create Database connection file
                $db_connect_content = "<?php
                require_once 'config.php';
                try {
                    \$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
                    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (PDOException \$e) {
                    die('DB ERROR: ' . \$e->getMessage());
                }
                ?>
                ";
                file_put_contents('db_connect.php', $db_connect_content);

                $stmt = $pdo->prepare("INSERT INTO pos_configuration (restaurant_name, restaurant_address, restaurant_phone, restaurant_email, opening_hours, closing_hours, tax_rate, currency, logo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$restaurant_name, $restaurant_address, $restaurant_phone, $restaurant_email, $opening_hours, $closing_hours, $tax_rate, $currency, $logo_path]);

                unset($_SESSION['step']);
                unset($_SESSION['install_data']);

                header('Location: index.php');
                exit;

            } catch (PDOException $e) {
                $errors[] = "Error: " . $e->getMessage();
            }
        }
    }
}

if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 1;
    $_SESSION['install_data'] = array();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP 8 POS System Installation Page</title>
    <link href="asset/vendor/bootstrap/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body>
    <main>
        <div class="container">
            <h1 class="mt-5 mb-5 text-center">PHP 8 POS System</h1>
            <div class="row">
            <div class="col-md-4">&nbsp;</div>
                <div class="col-md-4">                    
                    <?php if (!empty($errors)) { ?>
                        <div class="alert alert-danger">
                            <ul class="list-unstyled">
                                <?php foreach ($errors as $error) { ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                    <div class="card">
                        <?php if ($_SESSION['step'] == 1) { ?>
                        <div class="card-header"><b>Step 1: Database Configuration</b></div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="db_host" class="form-label">Database Host:</label>
                                    <input type="text" id="db_host" name="db_host" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="db_name" class="form-label">Database Name:</label>
                                    <input type="text" id="db_name" name="db_name" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="db_user" class="form-label">Database User:</label>
                                    <input type="text" id="db_user" name="db_user" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="db_pass" class="form-label">Database Password:</label>
                                    <input type="password" id="db_pass" name="db_pass" class="form-control">
                                </div>
                                <input type="submit" name="db_config" value="Next" class="btn btn-primary">
                            </form>
                        </div>
                        <?php } elseif ($_SESSION['step'] == 2) { ?>
                        <div class="card-header"><b>Step 2: Admin Account Creation</b></div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="user_name" class="form-label">Admin Name:</label>
                                    <input type="text" id="user_name" name="user_name" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="user_email" class="form-label">Admin Email:</label>
                                    <input type="email" id="user_email" name="user_email" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="user_password" class="form-label">Admin Password:</label>
                                    <input type="password" id="user_password" name="user_password" class="form-control">
                                </div>
                                <input type="submit" name="admin_account" value="Next" class="btn btn-primary">
                            </form>
                        </div>
                        <?php } elseif ($_SESSION['step'] == 3) { ?>
                        <div class="card-header"><b>Step 3: Restaurant Configuration</b></div>
                        <div class="card-body">
                            <form method="post" action="" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="restaurant_name" class="form-label">Restaurant Name:</label>
                                    <input type="text" id="restaurant_name" name="restaurant_name" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="restaurant_address" class="form-label">Restaurant Address:</label>
                                    <input type="text" id="restaurant_address" name="restaurant_address" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="restaurant_phone" class="form-label">Restaurant Phone:</label>
                                    <input type="text" id="restaurant_phone" name="restaurant_phone" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="restaurant_email" class="form-label">Restaurant Email:</label>
                                    <input type="email" id="restaurant_email" name="restaurant_email" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="opening_hours" class="form-label">Opening Hours:</label>
                                    <input type="text" id="opening_hours" name="opening_hours" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="closing_hours" class="form-label">Closing Hours:</label>
                                    <input type="text" id="closing_hours" name="closing_hours" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="tax_rate" class="form-label">Tax Rate:</label>
                                    <input type="text" id="tax_rate" name="tax_rate" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="currency" class="form-label">Currency:</label>
                                    <input type="text" id="currency" name="currency" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="currency" class="form-label">Logo:</label>
                                    <input type="file" name="logo" accept=".jpg, .jpeg, .png, .gif">
                                </div>
                                <input type="submit" name="restaurant_config" value="Finish" class="btn btn-primary">
                            </form>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>