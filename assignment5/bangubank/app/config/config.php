<?php
// config.php

return [

    'storage' => 'database', // Change to 'file' for file storage

    // Database configuration
    'db' => [
        'host' => 'localhost',
        'database' => 'your_database_name',
        'username' => 'your_username',
        'password' => 'your_password',
        'charset' => 'utf8mb4',
    ],

    // File storage configuration
    'filePath' => __DIR__ . '/../../storage/users.json',
    'transaction_FilePath' => __DIR__ . '/../../storage/transactions.json',
];
