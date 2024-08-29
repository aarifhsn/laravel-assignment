<?php

$config = require __DIR__ . '/config.php';

try {
    $dsn = "mysql:host={$config['db']['host']};charset={$config['db']['charset']}";

    $pdo = new PDO($dsn, $config['db']['username'], $config['db']['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create the database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$config['db']['database']}");
    $pdo->exec("USE {$config['db']['database']}");

    // Create tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        balance DECIMAL(10,2) DEFAULT 0
    )");

    // Create the transactions table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(255) NOT NULL,
        sender_email VARCHAR(255) NOT NULL,
        receiver_name VARCHAR(255) NOT NULL,
        receiver_email VARCHAR(255) NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        date DATETIME NOT NULL
    )");

    echo "Database and tables set up successfully!";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
