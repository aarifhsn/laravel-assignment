<?php

namespace Bangubank\Models;

class UserManager
{
    private $filePath = 'users.json';

    public function register($name, $email, $password)
    {
        $users = $this->getAllUsers();

        if ($this->emailExists($email)) {
            return false;
        }

        $userId = uniqid();

        $user = [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'balance' => 0
        ];

        $users[] = $user;

        return $this->saveUsers($users);
    }

    public function registeredByAdmin($fname, $lname, $email, $password)
    {
        $users = $this->getAllUsers();

        if ($this->emailExists($email)) {
            return false;
        }

        $userId = uniqid();

        $user = [
            'id' => $userId,
            'fname' => $fname,
            'lname' => $lname,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'balance' => 0
        ];

        $users[] = $user;

        return $this->saveUsers($users);
    }

    public function getAllUsers()
    {
        if (file_exists($this->filePath)) {
            $json = file_get_contents($this->filePath);
            return json_decode($json, true);
        }
        return [];
    }

    public function emailExists($email)
    {
        $users = $this->getAllUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return true;
            }
        }
        return false;
    }

    public function getUserByEmail($email)
    {
        $users = $this->getAllUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }

    public function updateUser($updatedUser)
    {
        $users = $this->getAllUsers();
        foreach ($users as &$user) {
            if ($user['email'] === $updatedUser['email']) {
                $user = $updatedUser;
                return $this->saveUsers($users);
            }
        }
        return false;
    }

    private function saveUsers($users)
    {
        if (file_put_contents($this->filePath, json_encode($users, JSON_PRETTY_PRINT))) {
            return true;
        } else {
            error_log("Failed to save users to file");
            return false;
        }
    }
}
