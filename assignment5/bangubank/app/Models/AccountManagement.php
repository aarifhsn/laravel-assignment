<?php

namespace Bangubank\Models;

use Bangubank\Models\User;
use Bangubank\Models\BalanceManager;
use PDO;
use PDOException;

date_default_timezone_set('Asia/Dhaka');

class AccountManagement
{
    private $transactionLogPath = __DIR__ . '/../../storage/transactions.json';
    protected $pdo;
    protected $user;
    protected $balanceManager;

    public function __construct($user, $pdo, $balanceManager)
    {
        $this->user = $user;
        $this->pdo = $pdo; // Assign the PDO connection
        $this->balanceManager = $balanceManager;
    }

    public function deposit($receiver_name, $email, $amount)
    {
        $user = $this->user->getLoggedInUser();
        if ($user) {
            if ($this->user->isDatabaseStorage()) {
                // Update the balance or insert a new user record if the email does not exist
                $stmt = $this->pdo->prepare("UPDATE users SET balance = balance + :amount WHERE email = :email");
                $stmt->execute(['amount' => $amount, 'email' => $email]);

                if ($stmt->rowCount() === 0) {
                    // User does not exist, so insert a new record
                    $stmt = $this->pdo->prepare("INSERT INTO users (name, email, balance) VALUES (:name, :email, :balance)");
                    $stmt->execute(['name' => $receiver_name, 'email' => $email, 'balance' => $amount]);
                }

                // Log the transaction
                $this->addTransaction([
                    'type' => 'deposit',
                    'sender_email' => $user['email'],
                    'receiver_name' => $receiver_name,
                    'receiver_email' => $email,
                    'amount' => $amount,
                    'date' => date('d M Y, h:i A')
                ]);
                return true;
            } else {
                // File storage logic
                $result = $this->user->updateUser($user);
                if ($result) {
                    $result = $this->balanceManager->updateBalance($receiver_name, $email, $amount);
                    if ($result) {
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
            }
        }
        return false;
    }

    public function withdraw($receiver_name, $email, $amount)
    {
        $user = $this->user->getLoggedInUser();
        if ($user) {
            if ($this->user->isDatabaseStorage()) {

                // Check if balance is sufficient
                $stmt = $this->pdo->prepare("SELECT balance FROM users WHERE email = :email");
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch();

                if ($user['balance'] >= $amount) {
                    // Update balance
                    $stmt = $this->pdo->prepare("UPDATE users SET balance = balance - :amount WHERE email = :email");
                    $stmt->execute(['amount' => $amount, 'email' => $email]);


                    // Log transaction
                    $this->addTransaction([
                        'type' => 'withdraw',
                        'sender_email' => $email,
                        'receiver_name' => $receiver_name,
                        'receiver_email' => $email,
                        'amount' => $amount,
                        'date' => date('d M Y, h:i A')
                    ]);

                    return true;

                } else {
                    // Log and handle insufficient balance
                    error_log('Insufficient balance for withdrawal.');
                    return false;
                }
            } else {
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
        if ($this->user->isDatabaseStorage()) {
            // Database storage logic
            $stmt = $this->pdo->query("SELECT * FROM transactions ORDER BY date DESC");
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $transactions ?: []; // Return an empty array if no transactions
        } else {
            $transactions = json_decode(file_get_contents($this->transactionLogPath), true);
            // Check if transactions were successfully retrieved
            if ($transactions === null) {
                error_log("Failed to retrieve transactions from file");
                return [];
            }

            // Sort transactions based on the 'date' key
            usort($transactions, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            return $transactions;
        }
    }

    public function addTransaction($transaction)
    {
        if ($this->user->isDatabaseStorage()) {
            // Database storage logic
            $stmt = $this->pdo->prepare("INSERT INTO transactions (user_id, type, sender_email, receiver_name, receiver_email, amount, date) VALUES (:user_id, :type, :sender_email, :receiver_name, :receiver_email, :amount, :date)");
            $stmt->execute([
                'user_id' => $this->user->getLoggedInUserID(),
                'type' => $transaction['type'],
                'sender_email' => $transaction['sender_email'],
                'receiver_name' => $transaction['receiver_name'],
                'receiver_email' => $transaction['receiver_email'],
                'amount' => $transaction['amount'],
                'date' => date('Y-m-d H:i:s')
            ]);
            return $stmt->rowCount() > 0;
        } else {
            $transactions = $this->getTransactions();
            $transactions[] = $transaction;
            $result = file_put_contents($this->transactionLogPath, json_encode($transactions, JSON_PRETTY_PRINT));
            return $result;
        }
    }

    public function getUserTransactionsByEmail($email)
    {
        if ($this->user->isDatabaseStorage()) {
            // Database storage logic
            $stmt = $this->pdo->prepare("SELECT * FROM transactions WHERE sender_email = :email OR receiver_email = :email ORDER BY date DESC");
            $stmt->execute(['email' => $email]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
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
}
