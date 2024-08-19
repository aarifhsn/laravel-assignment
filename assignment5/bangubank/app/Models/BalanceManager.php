<?php

namespace Bangubank;

class BalanceManager
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getBalance()
    {
        $user = $this->user->getLoggedInUser();
        return $user ? $user['balance'] : 0;
    }

    public function updateBalance($receiver_name = '', $email, $amount)
    {
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
            $users[] = [
                'name' => $receiver_name,
                'email' => $email,
                'balance' => $amount
            ];
            error_log("Created new user for email: $email with amount: $amount");
        }

        return file_put_contents($this->user->filePath, json_encode($users, JSON_PRETTY_PRINT)) ? true : false;
    }
}
