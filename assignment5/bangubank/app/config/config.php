<?php
// config.php

return [
    'storage' => 'database', // or 'file'
    'db' => [
        'host' => 'localhost',
        'database' => 'bangubank',
        'username' => 'root',
        'password' => '',
    ],
    'filePath' => __DIR__ . '/../../storage/users.json',
    'transaction_FilePath' => __DIR__ . '/../../storage/transactions.json',
];
