<?php

// Define storage method:
$storageMethod = 'database'; // Option - 'file' or 'database'

$host = 'localhost';
$db = 'bankofbangu';
$user = 'root';
$pass = '';

$filePath = __DIR__ . '/../../storage/users.json';
$transactionLogPath = __DIR__ . '/../../storage/transactions.json';


try {
    // Connect to the database (Connect to MySQL without specifying a database to create it if it doesn't exist)
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create the database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $db");
    $pdo->exec("USE $db");

    // Check if the tables exist by querying the information_schema
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$db' AND table_name = 'users'");
    $tableExists = $stmt->fetchColumn();

    if (!$tableExists) {

        // Create tables if they don't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(255) DEFAULT 'customer',
        balance DECIMAL(10,2) DEFAULT 0
    )");

        // Create the transactions table if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('deposit', 'withdraw', 'transfer'),
        sender_email VARCHAR(255) NOT NULL,
        receiver_name VARCHAR(255) NOT NULL,
        receiver_email VARCHAR(255) NOT NULL,
        amount DECIMAL(15, 2),
        date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
        echo "<div style='padding: 10px; background-color: #4CAF50; color: white; font-weight: bold; text-align: center;'>
        Database named {$db} and tables set up successfully!
      </div>";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}
// Return the configuration array, including the storage method
return [
    'pdo' => $pdo,
    'filePath' => $filePath,
    'transactionLogPath' => $transactionLogPath,
    'storageMethod' => $storageMethod, // Add the storage method here
];
