<?php

namespace Bangubank;

session_start();

class User
{
    public $filePath = 'users.json';

    public function register($name, $email, $password)
    {
        $users = $this->getAllUsers();

        // Check if email already exists
        if ($this->emailExists($email)) {
            return false;
        }

        // Generate a unique user ID
        $userId = uniqid();

        $user = [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'balance' => 0
        ];

        $users[] = $user;

        // Write updated data to file
        if (file_put_contents($this->filePath, json_encode($users, JSON_PRETTY_PRINT))) {
            return true;
        } else {
            error_log("Failed to write users to file");
            return false;
        }
    }

    public function registeredByAdmin($fname, $lname, $email, $password)
    {
        $users = $this->getAllUsers();

        // Check if email already exists
        if ($this->emailExists($email)) {
            return false;
        }

        // Generate a unique user ID
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

        // Write updated data to file
        if (file_put_contents($this->filePath, json_encode($users, JSON_PRETTY_PRINT))) {
            return true;
        } else {
            error_log("Failed to write users to file");
            return false;
        }
    }


    public function getAllUsers()
    {
        if (file_exists($this->filePath)) {
            $json = file_get_contents($this->filePath);
            return json_decode($json, true);
        }
        return [];
    }

    public function getName()
    {
        // Get user details based on the email stored in session
        $email = $_SESSION['email'] ?? '';
        $user = $this->getUserByEmail($email);
        return $user['name'] ?? $user['fname'] . ' ' . $user['lname'] ?? 'Guest';
    }

    public function getEmail()
    {
        return $_SESSION['email'] ?? '';
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

    public function login($email, $password)
    {
        $users = $this->getAllUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                $_SESSION['logged_in'] = true;
                $_SESSION['email'] = $email;
                $_SESSION['name'] = $user['name'];
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
                if (file_put_contents($this->filePath, json_encode($users, JSON_PRETTY_PRINT))) {
                    return true;
                } else {
                    error_log("Failed to update user");
                    return false;
                }
            }
        }
        return false;
    }

    public function getFirstChar($string)
    {
        preg_match_all('/\b\w/', $string, $matches);
        return implode('', $matches[0]);
    }

    public function getLoggedInUser()
    {
        if (isset($_SESSION['email'])) {
            return $this->getUserByEmail($_SESSION['email']);
        }
        return null;
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['email']);
    }

    public function logout()
    {
        session_destroy();
    }
}
