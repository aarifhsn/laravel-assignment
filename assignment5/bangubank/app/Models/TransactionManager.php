<?php

namespace Bangubank;

class TransactionManager
{
    private $transactionLogPath;

    public function __construct($transactionLogPath)
    {
        $this->transactionLogPath = $transactionLogPath;
    }

    public function getTransactions()
    {
        $transactions = json_decode(file_get_contents($this->transactionLogPath), true);
        if ($transactions === null) {
            error_log("Failed to retrieve transactions from file");
            return [];
        }

        usort($transactions, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        return $transactions;
    }

    public function addTransaction($transaction)
    {
        $transactions = $this->getTransactions();
        $transactions[] = $transaction;
        return file_put_contents($this->transactionLogPath, json_encode($transactions, JSON_PRETTY_PRINT));
    }

    public function getUserTransactionsByEmail($email)
    {
        $transactions = $this->getTransactions();
        return array_filter($transactions, function ($transaction) use ($email) {
            return $transaction['sender_email'] === $email || $transaction['receiver_email'] === $email;
        });
    }
}
