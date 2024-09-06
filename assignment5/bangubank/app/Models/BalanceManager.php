<?php

namespace Bangubank\Models;

use Bangubank\Models\User;
use PDO;
use PDOException;

class BalanceManager
{

    private $user;
    private $pdo;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->pdo = $this->user->isDatabaseStorage();
    }

    private function connectToDatabase()
    {
        $config = require __DIR__ . '/../../config.php';

        if ($config['storage'] === 'database') {
            try {
                $pdo = new PDO(
                    "mysql:host=" . $config['db']['host'] . ";dbname=" . $config['db']['database'],
                    $config['db']['username'],
                    $config['db']['password']
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $pdo;
            } catch (PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                return null;
            }
        }
        return null;
    }
    public function getPdo()
    {
        return $this->pdo;
    }
    public function getBalance()
    {
        $user = $this->user->getLoggedInUser();
        if ($user) {
            return $user['balance'];
        }
        return 0;
    }


    public function updateBalance($receiver_name, $email, $amount)
    {
        if ($this->pdo instanceof PDO) {
            // Database storage
            $stmt = $this->pdo->prepare("UPDATE transactions SET balance = balance + :amount WHERE email = :email");
            $stmt->execute(['amount' => $amount, 'email' => $email]);

            if ($stmt->rowCount()) {
                return true;
            } else {
                // If the user does not exist, insert them
                $stmt = $this->pdo->prepare("INSERT INTO transactions (name, email, balance) VALUES (:name, :email, :balance)");
                return $stmt->execute(['name' => $receiver_name, 'email' => $email, 'balance' => $amount]);
            }
        } else {
            $users = $this->user->getAllUsers();
            if ($users === null) {
                error_log("Failed to retrieve users from file");
                return false;
            }

            $userExists = false;
            foreach ($users as &$user) {
                if ($user['email'] === $email) {
                    $user['balance'] += $amount;
                    $userExists = true;
                    break;
                }
            }

            if (!$userExists) {
                // Create a new user entry if the recipient does not exist
                $users[] = [
                    'name' => $receiver_name,
                    'email' => $email,
                    'balance' => $amount
                ];
                error_log("Created new user for email: $email with amount: $amount");
            }

            if (file_put_contents($this->user->getFilePath(), json_encode($users, JSON_PRETTY_PRINT))) {
                return true;
            } else {
                error_log("Failed to update user balance in file");
                return false;
            }
        }
    }
}
