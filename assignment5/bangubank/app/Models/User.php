<?php

namespace Bangubank\Models;
use PDO;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
class User
{
    public $filePath = '/../app/config/config.php';
    private $pdo;

    public function __construct($pdo = null, $filePath = null)
    {
        if ($pdo && !($pdo instanceof PDO)) {
            throw new \Exception("Expected a PDO instance, got " . gettype($pdo));
        }
        $this->pdo = $pdo;
        $this->filePath = $filePath;
    }

    public function getFilePath()
    {
        if ($this->filePath) {
            return $this->filePath;
        }
        throw new \RuntimeException('File storage is not configured.');
    }

    public function isDatabaseStorage()
    {
        return $this->pdo !== null;
    }

    public function register($name, $email, $password)
    {
        if ($this->isDatabaseStorage()) {
            // Use database storage
            $query = "INSERT INTO users ( name, email, password, balance) VALUES ( :name, :email, :password, :balance)";
            $stmt = $this->pdo->prepare($query);

            $params = [
                ':name' => $name,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':balance' => 0
            ];
            return $stmt->execute($params);
        } elseif ($this->filePath) {

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

            // Write updated data to file or storage
            return file_put_contents($this->filePath, json_encode($users, JSON_PRETTY_PRINT));
        } else {
            throw new \RuntimeException('File storage is not configured.');
        }
    }

    public function registeredByAdmin($fname, $lname, $email, $password)
    {
        if ($this->isDatabaseStorage()) {

            // Concatenate first and last name to form the full name
            $fullName = trim($fname . ' ' . $lname);

            // Database storage
            $query = "INSERT INTO users (name, email, password, balance) VALUES (:name, :email, :password, :balance)";
            $stmt = $this->pdo->prepare($query);

            $params = [
                ':name' => $fullName,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':balance' => 0
            ];

            return $stmt->execute($params);
        } elseif ($this->filePath) {
            // File storage
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
            return file_put_contents($this->filePath, json_encode($users, JSON_PRETTY_PRINT));
        } else {
            throw new \RuntimeException('File storage is not configured.');
        }
    }

    public function getLoggedInUserID()
    {
        $user = $this->getLoggedInUser();
        if (isset($user['id'])) {
            return $user['id'];
        }
        return null;
    }
    public function getAllUsers()
    {
        if ($this->isDatabaseStorage()) {
            $stmt = $this->pdo->query("SELECT * FROM users");
            return $stmt->fetchAll();
        } elseif ($this->filePath) {
            if (file_exists($this->filePath)) {
                $json = file_get_contents($this->filePath);
                return json_decode($json, true);
            }
        } else {
            throw new \RuntimeException('File storage is not configured.');
        }

        return [];
    }
    public function getUserByEmail($email)
    {

        if ($this->pdo) {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            return $stmt->fetch();
        } elseif ($this->filePath) {
            $users = $this->getAllUsers() ?? [];
            foreach ($users as $user) {
                if ($user['email'] === $email) {
                    return $user;
                }
            }
        } else {
            throw new \RuntimeException('File storage is not configured.');
        }

        return null;
    }
    public function getName()
    {
        $user = $this->getLoggedInUser();
        if (isset($user['fname']) && isset($user['lname'])) {
            return $user['fname'] . ' ' . $user['lname'];
        }
        return $user['name'] ?? 'Guest';
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
        $user = $this->getUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $this->getName();

            return true;
        }

        return false;
    }

    public function updateUser($updatedUser)
    {
        if ($this->isDatabaseStorage()) {
            $query = "UPDATE users SET name = :name, email = :email, password = :password, balance = :balance WHERE id = :id";
            $stmt = $this->pdo->prepare($query);
            $params = [
                ':name' => $updatedUser['name'],
                ':email' => $updatedUser['email'],
                ':password' => $updatedUser['password'],
                ':balance' => $updatedUser['balance'],
                ':id' => $updatedUser['id']
            ];
            return $stmt->execute($params);


        } elseif ($this->filePath) {
            $users = $this->getAllUsers();
            foreach ($users as &$user) {
                if ($user['id'] === $updatedUser['id']) {
                    $user = $updatedUser;
                    return file_put_contents($this->filePath, json_encode($users, JSON_PRETTY_PRINT)) !== false;
                }
            }
        } else {
            throw new \RuntimeException('File storage is not configured.');
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
