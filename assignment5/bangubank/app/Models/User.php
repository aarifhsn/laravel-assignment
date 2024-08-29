<?php

namespace Bangubank\Models;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
class User
{
    private $filePath;
    private $pdo;

    public function __construct(array $config)
    {
        if (empty($config['storage'])) {
            throw new \InvalidArgumentException('Invalid configuration: storage type not set.');
        }

        if ($config['storage'] === 'file') {
            if (isset($config['filePath'])) {
                $this->filePath = $config['filePath'];
            } else {
                throw new \InvalidArgumentException('Invalid configuration: filePath not set.');
            }
            $this->pdo = null; // Disable PDO when using file storage
        } elseif ($config['storage'] === 'database') {
            $this->pdo = require __DIR__ . '/../config/db_setup.php';
            $this->filePath = null; // Disable file storage when using database
        } else {
            throw new \InvalidArgumentException('Invalid configuration: unknown storage type.');
        }
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

        if ($this->pdo) {
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

        if ($this->pdo) {
            // Database storage
            $query = "INSERT INTO users (id, fname, lname, email, password, balance) VALUES (:id, :fname, :lname, :email, :password, :balance)";
            $stmt = $this->pdo->prepare($query);
            $params = [
                ':id' => $userId,
                ':fname' => $fname,
                ':lname' => $lname,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':balance' => 0
            ];
            return $stmt->execute($params);
        } elseif ($this->filePath) {
            // File storage
            $users[] = $user;

            return file_put_contents($this->filePath, json_encode($users, JSON_PRETTY_PRINT));
        } else {
            throw new \RuntimeException('File storage is not configured.');
        }
    }
    public function getAllUsers()
    {
        if ($this->pdo) {
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
            $stmt->execute([':email' => $email]);
            return $stmt->fetch();
        } elseif ($this->filePath) {
            $users = $this->getAllUsers();
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
        $users = $this->getUserByEmail($email);
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

    public function updateUser($updatedUser)
    {
        if ($this->pdo) {
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
                if ($user['email'] === $updatedUser['email']) {
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
