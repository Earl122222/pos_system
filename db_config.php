<?php
// Database Mode Configuration
define('DB_MODE', 'local'); // Can be 'local' or 'online'

// Local Database Configuration
define('LOCAL_DB_HOST', 'localhost');
define('LOCAL_DB_NAME', 'pos');
define('LOCAL_DB_USER', 'root');
define('LOCAL_DB_PASS', '');

// Online Database Configuration
define('ONLINE_DB_HOST', ''); // Your online database host
define('ONLINE_DB_NAME', ''); // Your online database name
define('ONLINE_DB_USER', ''); // Your online database username
define('ONLINE_DB_PASS', ''); // Your online database password

// Set active configuration based on mode
define('DB_HOST', DB_MODE === 'local' ? LOCAL_DB_HOST : ONLINE_DB_HOST);
define('DB_NAME', DB_MODE === 'local' ? LOCAL_DB_NAME : ONLINE_DB_NAME);
define('DB_USER', DB_MODE === 'local' ? LOCAL_DB_USER : ONLINE_DB_USER);
define('DB_PASS', DB_MODE === 'local' ? LOCAL_DB_PASS : ONLINE_DB_PASS);

// Function to switch database mode
function switchDatabaseMode($mode) {
    if (!in_array($mode, ['local', 'online'])) {
        throw new Exception('Invalid database mode. Use "local" or "online".');
    }
    
    $config_file = __DIR__ . '/db_config.php';
    $content = file_get_contents($config_file);
    
    // Update the DB_MODE definition
    $content = preg_replace(
        "/define\('DB_MODE',\s*'[^']+'\);/",
        "define('DB_MODE', '$mode');",
        $content
    );
    
    return file_put_contents($config_file, $content);
}

// Function to test database connection
function testDatabaseConnection($mode = null) {
    $mode = $mode ?? DB_MODE;
    $host = $mode === 'local' ? LOCAL_DB_HOST : ONLINE_DB_HOST;
    $dbname = $mode === 'local' ? LOCAL_DB_NAME : ONLINE_DB_NAME;
    $user = $mode === 'local' ? LOCAL_DB_USER : ONLINE_DB_USER;
    $pass = $mode === 'local' ? LOCAL_DB_PASS : ONLINE_DB_PASS;
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return true;
    } catch (PDOException $e) {
        return false;
    }
} 