<?php

require_once 'FileManager.php';

class User
{
    private $users = [];
    private $userFile = 'data/users.json';
    private $fileManager;

    public function __construct()
    {
        $this->fileManager = new FileManager($this->userFile);
        $this->users = $this->fileManager->read();
        session_start();
    }

    public function userExist($email)
    {
        foreach ($this->users as $user) {
            if ($user['email'] === $email) {
                return true;
            }
        }
        return false;
    }

    public function register($name, $email, $password)
    {
        if (!$this->userExist($name)) {
            $newUser = [
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ];
            $this->users[] = $newUser;
            $this->fileManager->write($this->users);
            return true;
        }
        return false;
    }

    private function getAllUsers()
    {
        $jsonData = file_get_contents($this->userFile);
        return json_decode($jsonData, true);
    }
    public function login($email, $password)
    {
        $users = $this->getAllUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                $_SESSION['logged_in'] = true;
                $_SESSION['email'] = $email;
                $_SESSION['name'] = $user['name']; // Store user's name in session
                return true;
            }
        }
        return false;
    }
    public function getUserName()
    {
        return $_SESSION['name'] ?? 'Guest';
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public function logout()
    {
        session_destroy();
    }
}
