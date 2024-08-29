<?php

namespace Bangubank\Models;

use Bangubank\Models\User;
use Bangubank\Models\BalanceManager;
use PDO;

date_default_timezone_set('Asia/Dhaka');

class AccountManagement
{
    private $storageType;
    private $transactionLogPath;
    private $user;
    private $balanceManager;
    private $dbConnection;


    public function __construct(User $user)
    {
        $config = require __DIR__ . '/../config/config.php';
        $this->storageType = $config['storage'];
        $this->transactionLogPath = $config['transaction_FilePath'];
        $this->user = $user;
        $this->balanceManager = new BalanceManager($user);

        if ($this->storageType === 'database') {
            $this->dbConnection = new PDO('mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['database'], $config['db']['username'], $config['db']['password']);
            $this->dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    public function deposit($receiver_name, $email, $amount)
    {
        $user = $this->user->getLoggedInUser();
        if ($user) {
            $result = $this->user->updateUser($user);
            if ($result) {
                $result = $this->balanceManager->updateBalance($receiver_name, $email, $amount);

                $this->addTransaction([
                    'type' => 'deposit',
                    'sender_email' => $user['email'],
                    'receiver_name' => $user['name'],
                    'receiver_email' => $email,
                    'amount' => $amount,
                    'date' => date('d M Y, h:i A')
                ]);
                return true;
            }
        }
        return false;
    }


    public function withdraw($receiver_name, $email, $amount)
    {
        $user = $this->user->getLoggedInUser();
        if ($user) {
            // Check if the user has enough balance to withdraw the amount
            if ($user['balance'] >= $amount) {
                $user['balance'] -= $amount;
                $result = $this->user->updateUser($user);

                if ($result) {
                    $this->addTransaction([
                        'type' => 'withdraw',
                        'sender_email' => $user['email'],
                        'receiver_name' => $receiver_name,
                        'receiver_email' => $email,
                        'amount' => $amount,
                        'date' => date('d M Y, h:i A')
                    ]);
                }
                return $result;
            } else {
                // Log and handle insufficient balance
                error_log('Insufficient balance for withdrawal.');
                return false;
            }
        }
        return false;
    }


    public function transfer($receiver_name, $email, $amount)
    {
        $user = $this->user->getLoggedInUser();
        if ($user) {
            if ($user['balance'] < $amount) {
                return false; // Insufficient balance
            }

            $user['balance'] -= $amount;
            $result = $this->user->updateUser($user);

            if ($result) {
                $result = $this->balanceManager->updateBalance($receiver_name, $email, $amount);
                $this->addTransaction([
                    'type' => 'transfer',
                    'sender_email' => $user['email'],
                    'receiver_name' => $receiver_name,
                    'receiver_email' => $email,
                    'amount' => $amount,
                    'date' => date('d M Y, h:i A')
                ]);
            }
            return $result;
        }
        return false;
    }

    public function getTransactions()
    {
        if ($this->storageType === 'file') {
            $transactions = json_decode(file_get_contents($this->transactionLogPath), true);
            // Check if transactions were successfully retrieved
            if ($transactions === null) {
                error_log("Failed to retrieve transactions from file");
                return [];
            }
        } elseif ($this->storageType === 'database') {
            $stmt = $this->dbConnection->prepare("SELECT * FROM transactions");
            // $stmt->execute();
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }


        // Sort transactions based on the 'date' key
        usort($transactions, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        return $transactions;
    }

    public function addTransaction($transaction)
    {
        if ($this->storageType === 'file') {
            $transactions = $this->getTransactions();
            $transactions[] = $transaction;
            $result = file_put_contents($this->transactionLogPath, json_encode($transactions, JSON_PRETTY_PRINT));
            return $result;
        } elseif ($this->storageType === 'database') {
            $stmt = $this->dbConnection->prepare("INSERT INTO transactions (type, sender_email, receiver_name, receiver_email, amount, date) VALUES (:type, :sender_email, :receiver_name, :receiver_email, :amount, :date)");
            return $stmt->execute([
                ':type' => $transaction['type'],
                ':sender_email' => $transaction['sender_email'],
                ':receiver_name' => $transaction['receiver_name'],
                ':receiver_email' => $transaction['receiver_email'],
                ':amount' => $transaction['amount'],
                ':date' => $transaction['date']
            ]);
        }

        return false;
    }

    public function getUserTransactionsByEmail($email)
    {
        $transactions = $this->getTransactions(); // Get all transactions
        $userTransactions = [];

        // Filter transactions by the provided email
        foreach ($transactions as $transaction) {
            if ($transaction['sender_email'] === $email || $transaction['receiver_email'] === $email) {
                $userTransactions[] = $transaction;
            }
        }

        return $userTransactions;
    }
}
