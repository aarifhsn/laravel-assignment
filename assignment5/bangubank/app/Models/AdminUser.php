<?php

namespace Bangubank\Models;

use PDO;

class AdminUser
{
    private $filePath;
    private $pdo;
    private $storageMethod;

    public function __construct($filePath, PDO $pdo = null, $storageMethod = 'file')
    {
        $this->filePath = $filePath;
        $this->pdo = $pdo;
        $this->storageMethod = $storageMethod;
    }

    // Fetch all users from either file or database
    public function getAllUsers()
    {
        if ($this->storageMethod === 'file') {
            if (file_exists($this->filePath)) {
                return json_decode(file_get_contents($this->filePath), true);
            }
            return [];
        } else if ($this->storageMethod === 'database') {
            $stmt = $this->pdo->query("SELECT * FROM users");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }


    public function saveUsers($users)
    {
        if (file_put_contents($this->filePath, json_encode($users, JSON_PRETTY_PRINT))) {
            return true;
        } else {
            return false;
        }
    }

    public function createAdmin($name, $email, $password)
    {
        if ($this->storageMethod === 'file') {
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
                'balance' => 0.00
            ];

            $users[] = $admin;
            // Attempt to save the users
            if ($this->saveUsers($users)) {
                echo "Admin user created successfully.\n";
                return true;
            } else {
                echo "Failed to create admin user. Please try again.\n";
                return false;
            }
        } else if ($this->storageMethod === 'database') {
            $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role, balance) VALUES (:name, :email, :password, :role, :balance)");

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            if ($stmt->execute(['name' => $name, 'email' => $email, 'password' => $hashedPassword, 'role' => 'admin', 'balance' => 0.00])) {
                echo "Admin user created successfully.\n";
                return true;
            } else {
                echo "Failed to create admin user. Please try again.\n";
                return false;
            }
        }

    }

    // Retrieve admin user by email from file or database
    public function getAdminUser($email)
    {
        if ($this->storageMethod === 'file') {
            $users = $this->getAllUsers();
            foreach ($users as $user) {
                if (isset($user['role']) && $user['role'] === 'admin' && $user['email'] === $email) {
                    return $user;
                }
            }
        } else if ($this->storageMethod === 'database') {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email AND role = 'admin'");
            $stmt->execute(['email' => $email]); // Execute the query with the email $email);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    //check if admin is loggedIn
    public function adminLoggedIn()
    {
        if (isset($_SESSION['email'])) {
            $admin_user = $this->getAdminUser($_SESSION['email']);
            return $admin_user ? true : false;
        }
        return false;
    }

    // Admin login functionality
    public function adminLogin($email, $password)
    {
        $admin_user = $this->getAdminUser($email);
        if ($admin_user && password_verify($password, $admin_user['password'])) {
            $_SESSION['email'] = $email;
            return true;
        }
        return false;
    }

    public function getAdminName()
    {
        if ($this->adminLoggedIn()) {
            $admin_user = $this->getAdminUser($_SESSION['email']);
            return $admin_user ? $admin_user['name'] : 'Unknown Admin';
        }
    }

    public function adminLogout()
    {
        session_unset();
        session_destroy();
    }
}
