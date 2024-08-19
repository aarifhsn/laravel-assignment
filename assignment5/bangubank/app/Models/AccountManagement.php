<?php

namespace Bangubank;

use Bangubank\User;
use Bangubank\TransactionManager;
use Bangubank\BalanceManager;

class AccountManagement
{
    private $user;
    private $transactionManager;
    private $balanceManager;

    public function __construct(User $user, TransactionManager $transactionManager, BalanceManager $balanceManager)
    {
        $this->user = $user;
        $this->transactionManager = $transactionManager;
        $this->balanceManager = $balanceManager;
    }

    public function deposit($receiver_name = '', $email, $amount)
    {
        $user = $this->user->getLoggedInUser();
        if ($user) {
            $result = $this->balanceManager->updateBalance($receiver_name, $email, $amount);
            if ($result) {
                $this->transactionManager->addTransaction([
                    'type' => 'deposit',
                    'sender_email' => $user['email'],
                    'receiver_name' => $user['name'],
                    'receiver_email' => $email,
                    'amount' => $amount,
                    'date' => date('d M Y, h:i A')
                ]);
            }
            return $result;
        }
        return false;
    }

    public function withdraw($receiver_name, $email, $amount)
    {
        $user = $this->user->getLoggedInUser();
        if ($user && $user['balance'] >= $amount) {
            $user['balance'] -= $amount;
            $result = $this->balanceManager->updateBalance($receiver_name, $email, -$amount);
            if ($result) {
                $this->transactionManager->addTransaction([
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
            error_log('Insufficient balance for withdrawal.');
            return false;
        }
    }

    public function transfer($receiver_name = '', $email, $amount)
    {
        $user = $this->user->getLoggedInUser();
        if ($user && $user['balance'] >= $amount) {
            $user['balance'] -= $amount;
            $result = $this->balanceManager->updateBalance($receiver_name, $email, $amount);
            if ($result) {
                $this->transactionManager->addTransaction([
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

    public function getBalance()
    {
        return $this->balanceManager->getBalance();
    }

    public function getUserTransactionsByEmail($email)
    {
        return $this->transactionManager->getUserTransactionsByEmail($email);
    }
}
