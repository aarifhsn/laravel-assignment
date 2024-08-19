<?php

require_once __DIR__ . '/vendor/autoload.php';

use Bangubank\AdminUser;

// Define the path to the users.json file
$filePath = __DIR__ . '/users.json';

// Create the User object
$admin_user = new AdminUser($filePath);

// Prompt for admin details
echo "Enter admin name: ";
$name = trim(fgets(STDIN));

echo "Enter admin email: ";
$email = trim(fgets(STDIN));

echo "Enter admin password: ";
$password = trim(fgets(STDIN));

// Create the admin user
$admin_user->createAdmin($name, $email, $password);
