<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

// Fetch category for the dropdown
$categorys = $pdo->query("SELECT category_id, category_name FROM pos_category WHERE category_status = 'Active'")->fetchAll(PDO::FETCH_ASSOC);

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $errors = [];

    $category_id = $_POST['category_id'];
    $product_name = trim($_POST['product_name']);
    $product_image = $_FILES['product_image'];
    $product_price = trim($_POST['product_price']);
    $product_status = $_POST['product_status'];
    $destPath = '';

    // Validate fields
    if (empty($category_id)) {
        $errors[] = 'Category is required.';
    }
    if (empty($product_name)) {
        $errors[] = 'Product Name is required.';
    }
    if (empty($product_price)) {
        $errors[] = 'Product Price is required.';
    }

    // Check if Product already exists for another user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_product WHERE product_name = :product_name");
    $stmt->execute(['product_name' => $product_name]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $errors[] = 'Product already exists.';
    } else {
        // Handle image upload
        if ($product_image['error'] === UPLOAD_ERR_OK) {

            // Define the allowed file types
            $allowedTypes = ['image/jpeg', 'image/png'];

            // Get the uploaded file information
            $fileTmpPath = $product_image['tmp_name'];
            $fileName = $product_image['name'];
            $fileSize = $product_image['size'];
            $fileType = $product_image['type'];

            // Validate the file type
            if (in_array($fileType, $allowedTypes)) {
                // Define the upload directory
                $uploadDir = 'uploads/';

                // Generate a unique file name to avoid overwriting
                $uniqueFileName = uniqid('', true) . '-' . basename($fileName);

                // Define the destination path
                $destPath = $uploadDir . $uniqueFileName;

                // Move the uploaded file to the destination directory
                move_uploaded_file($fileTmpPath, $destPath);
            } else {
                $errors[] = "Invalid file type. Only JPG and PNG files are allowed.";
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO pos_product (category_id, product_name, product_image, product_price, product_status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$category_id, $product_name, $destPath, $product_price, $product_status]);
        header("Location: product.php");
        exit;
    } else {
        $message = '<ul class="list-unstyled">';
        foreach ($errors as $error) {
            $message .= '<li>' . $error . '</li>';
        }
        $message .= '</ul>';
    }
}

include('header.php');

?>

<h1 class="mt-4">Add Product</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="product.php">Product Management</a></li>
    <li class="breadcrumb-item active">Add Product</li>
</ol>
<?php
if($message !== ''){
    echo '<div class="alert alert-danger">'.$message.'</div>';
}
?>
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Add Product</div>
                <div class="card-body">
                    <form method="POST" action="add_product.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" class="form-select">
                                <option value="">Select Category</option>
                                <?php foreach ($categorys as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="product_name">Product Name</label>
                            <input type="text" name="product_name" id="product_name" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="product_price">Product Price</label>
                            <input type="number" name="product_price" id="product_price" class="form-control" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="product_status">Status</label>
                            <select name="product_status" id="product_status" class="form-select">
                                <option value="Available">Available</option>
                                <option value="Out of Stock">Out of Stock</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="product_image">Image</label>
                            <input type="file" name="product_image" accept="image/*" />
                        </div>
                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-primary">Add Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>
