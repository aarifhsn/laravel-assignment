<?php

require_once __DIR__ . '/vendor/autoload.php';

use Bangubank\Models\AdminUser;

// load configuration
$config = require __DIR__ . '/app/config/config.php';

// Create the User object
$admin_user = new AdminUser($config['filePath'], $config['pdo'], $config['storageMethod']);

// Prompt for admin details
echo "Enter admin name: ";
$name = trim(fgets(STDIN));

do {
    echo "Enter admin email: ";
    $email = trim(fgets(STDIN));

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format. Please try again.\n";
    } else {
        break;
    }
} while (true);

echo "Enter admin password: ";
$password = trim(fgets(STDIN));

// Create the admin user
$admin_user->createAdmin($name, $email, $password);
