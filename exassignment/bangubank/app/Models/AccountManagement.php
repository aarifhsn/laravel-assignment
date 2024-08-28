<?php

namespace Bangubank\Models;

use Bangubank\Models\User;
use Bangubank\Models\BalanceManager;

date_default_timezone_set('Asia/Dhaka');

class AccountManagement
{
    private $transactionLogPath = __DIR__ . '/../../storage/transactions.json';
    private $user;

    private $balanceManager;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->balanceManager = new BalanceManager($user);
    }

    public function deposit($receiver_name, $email, $amount)
    {
        $user = $this->user->getLoggedInUser();
        if ($user) {
            $result = $this->user->updateUser($user);
            if ($result) {
                error_log("Updating balance for $receiver_name with email $email");
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
                } else {
                    error_log("Balance update failed for $receiver_name");
                }
            } else {
                error_log("User update failed for " . print_r($user, true));
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

    public function addTransaction($transaction)
    {
        $transactions = $this->getTransactions();
        $transactions[] = $transaction;
        $result = file_put_contents($this->transactionLogPath, json_encode($transactions, JSON_PRETTY_PRINT));
        return $result;
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
