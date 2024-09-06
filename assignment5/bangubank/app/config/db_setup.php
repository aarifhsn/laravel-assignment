<?php

return [
    'storage' => 'database', // Options: 'database', 'file'
    
    'db' => [
        'host' => 'localhost',
        'database' => 'bangubank',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],

    'filePath' => __DIR__ . '/../../storage/users.json',
    'transaction_FilePath' => __DIR__ . '/../../storage/transactions.json',
];