<?php

$config = require 'config.php';

try {

    $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['database']};charset={$config['db']['charset']}, {$config['db']['username']}, {$config['db']['password']}";
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create the database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$config['db']['database']}");

    // Connect to the newly created database
    $pdo->exec("USE {$config['db']['database']}");

    // Create tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fname VARCHAR(255) NOT NULL,
        lname VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'customer') NOT NULL
    )");

    echo "Database and tables created successfully!";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
