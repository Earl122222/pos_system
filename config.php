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
?>
