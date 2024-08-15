<?php

namespace Bangubank;

class AdminUser
{
    public $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function getAllUsers()
    {
        if (file_exists($this->filePath)) {
            return json_decode(file_get_contents($this->filePath), true);
        }
        return [];
    }

    public function getAdminUser($email)
    {
        $users = $this->getAllUsers();
        foreach ($users as $user) {
            if ($user['role'] === 'admin' && $user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }

    public function saveUsers($users)
    {
        file_put_contents($this->filePath, json_encode($users, JSON_PRETTY_PRINT));
    }

    public function createAdmin($name, $email, $password)
    {
        $users = $this->getAllUsers();

        foreach ($users as $user) {
            if ($user['email'] === $email) {
                echo "User with this email already exists.\n";
                return false;
            }
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $admin = [
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $users[] = $admin;
        $this->saveUsers($users);

        echo "Admin user created successfully.\n";
        return true;
    }

    public function adminLoggedIn()
    {
        if (isset($_SESSION['email'])) {
            $admin_user = $this->getAdminUser($_SESSION['email']);
            if ($admin_user) {
                return true;
            }
        }
        return false;
    }

    public function adminLogin($email, $password)
    {
        $users = $this->getAllUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['email'] = $email;
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }
    public function getAdminName()
    {
        if ($this->adminLoggedIn()) {
            $admin_user = $this->getAdminUser($_SESSION['email']);
            return $admin_user['name'];
        }
    }

    public function adminLogout()
    {
        session_unset();
        session_destroy();
    }
}
