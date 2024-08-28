<?php

namespace Bangubank\Models;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//require __DIR__ . '/../config/config.php';

class User
{

    private $filePath;
    private $pdo;

    public function __construct($config)
    {
        if (isset($config['filePath'])) {
            $this->filePath = $config['filePath'];
        } else {
            throw new \InvalidArgumentException('Invalid configuration: filePath not set.');
        }

        if (isset($config['storage']) && $config['storage'] === 'database') {
            $this->pdo = new \PDO(
                'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['database'],
                $config['db']['username'],
                $config['db']['password']
            );
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } else {
            $this->pdo = null;
        }
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function isDatabaseStorage()
    {
        $config = include_once __DIR__ . '/../config/config.php';
        if ($config === false) {
            throw new \Exception('Failed to load config file');
        }
        return $config['storage'] === 'database';
    }

    public function register($name, $email, $password)
    {
        $users = $this->getAllUsers();

        // Check if email already exists
        if ($this->emailExists($email)) {
            return false;
        }

        // Generate a unique user ID
        $userId = uniqid();

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
        } else {
            $user = [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'balance' => 0
            ];
        }

        $users[] = $user;

        // Write updated data to file or storage

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
        if ($this->pdo) {
            $stmt = $this->pdo->query("SELECT * FROM users");
            return $stmt->fetchAll();
        } elseif (file_exists($this->filePath)) {
            $json = file_get_contents($this->filePath);
            return json_decode($json, true);
        }
        return [];
    }

    public function getUserByEmail($email)
    {

        if ($this->pdo) {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } else {
            $users = $this->getAllUsers();
            foreach ($users as $user) {
                if ($user['email'] === $email) {
                    return $user;
                }
            }
        }

        return null;
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



    public function updateUser($updatedUser)
    {
        if ($this->pdo) {
            $query = "UPDATE users SET name = :name, email = :email, password = :password, balance = :balance WHERE email = :email";
            $stmt = $this->pdo->prepare($query);
            $params = [
                ':name' => $updatedUser['name'],
                ':email' => $updatedUser['email'],
                ':password' => $updatedUser['password'],
                ':balance' => $updatedUser['balance'],
            ];
            return $stmt->execute($params);
        } else {
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
