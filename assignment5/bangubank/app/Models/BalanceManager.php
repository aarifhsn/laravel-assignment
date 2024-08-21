<?php

namespace Bangubank\Models;

use Bangubank\Models\User;

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
        if ($user) {
            return $user['balance'];
        }
        return 0;
    }

    public function updateBalance($receiver_name, $email, $amount)
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
