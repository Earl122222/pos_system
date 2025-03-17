<?php
require_once 'db_config.php';
session_start();

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mode']) && in_array($_POST['mode'], ['local', 'online'])) {
        try {
            // Test the new connection first
            if (testDatabaseConnection($_POST['mode'])) {
                switchDatabaseMode($_POST['mode']);
                $message = 'Database mode switched to ' . $_POST['mode'] . ' successfully!';
            } else {
                $error = 'Cannot switch to ' . $_POST['mode'] . ' mode: Connection test failed';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    if (isset($_POST['update_online_config'])) {
        $config_file = __DIR__ . '/db_config.php';
        $content = file_get_contents($config_file);
        
        // Update online database configuration
        $content = preg_replace(
            "/define\('ONLINE_DB_HOST',\s*'[^']*'\);/",
            "define('ONLINE_DB_HOST', '" . addslashes($_POST['host']) . "');",
            $content
        );
        $content = preg_replace(
            "/define\('ONLINE_DB_NAME',\s*'[^']*'\);/",
            "define('ONLINE_DB_NAME', '" . addslashes($_POST['dbname']) . "');",
            $content
        );
        $content = preg_replace(
            "/define\('ONLINE_DB_USER',\s*'[^']*'\);/",
            "define('ONLINE_DB_USER', '" . addslashes($_POST['username']) . "');",
            $content
        );
        $content = preg_replace(
            "/define\('ONLINE_DB_PASS',\s*'[^']*'\);/",
            "define('ONLINE_DB_PASS', '" . addslashes($_POST['password']) . "');",
            $content
        );
        
        if (file_put_contents($config_file, $content)) {
            $message = 'Online database configuration updated successfully!';
        } else {
            $error = 'Failed to update online database configuration';
        }
    }
}

// Get current mode
$current_mode = DB_MODE;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Mode Management</title>
    <link href="asset/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 800px; margin-top: 50px; }
        .card { margin-bottom: 20px; }
        .current-mode { font-size: 1.2em; margin-bottom: 20px; }
        .mode-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .mode-local { background-color: #e3f2fd; color: #0d47a1; }
        .mode-online { background-color: #f1f8e9; color: #33691e; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Database Mode Management</h2>
        
        <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="current-mode">
            Current Mode: 
            <span class="mode-badge mode-<?php echo $current_mode; ?>">
                <?php echo ucfirst($current_mode); ?>
            </span>
        </div>
        
        <div class="card">
            <div class="card-header">Switch Database Mode</div>
            <div class="card-body">
                <form method="post" class="mb-3">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="mode" value="local" id="modeLocal" <?php echo $current_mode === 'local' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="modeLocal">Local Database</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="mode" value="online" id="modeOnline" <?php echo $current_mode === 'online' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="modeOnline">Online Database</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Switch Mode</button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">Online Database Configuration</div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="host" class="form-label">Database Host</label>
                        <input type="text" class="form-control" id="host" name="host" value="<?php echo ONLINE_DB_HOST; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="dbname" class="form-label">Database Name</label>
                        <input type="text" class="form-control" id="dbname" name="dbname" value="<?php echo ONLINE_DB_NAME; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo ONLINE_DB_USER; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" value="<?php echo ONLINE_DB_PASS; ?>" required>
                    </div>
                    <button type="submit" name="update_online_config" class="btn btn-primary">Update Online Configuration</button>
                </form>
            </div>
        </div>
        
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html> 