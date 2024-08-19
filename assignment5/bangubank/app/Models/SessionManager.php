<?php

namespace Bangubank;

class SessionManager
{
    public function __construct()
    {
        session_start();
    }

    public function login($email, $password, UserManager $userManager)
    {
        $user = $userManager->getUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $user['name'] ?? $user['fname'] . ' ' . $user['lname'];
            return true;
        }
        return false;
    }

    public function logout()
    {
        session_destroy();
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['email']);
    }

    public function getEmail()
    {
        return $_SESSION['email'] ?? '';
    }

    public function getName()
    {
        return $_SESSION['name'] ?? 'Guest';
    }
}
