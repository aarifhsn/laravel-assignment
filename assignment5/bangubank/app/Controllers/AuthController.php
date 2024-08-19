<?php

namespace Bangubank\Controllers;

use Bangubank\Models\UserManager;

class AuthController
{
    private $userManager;

    public function __construct()
    {
        $this->userManager = new UserManager();
    }

    public function register()
    {
        $error = [];
        $fname = $lname = $email = $password = '';

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $fname = $this->sanitize($_POST['first-name']);
            $lname = $this->sanitize($_POST['last-name']);
            $email = $this->sanitize($_POST['email']);
            $password = $this->sanitize($_POST['password']);

            $error = $this->validate($fname, $lname, $email, $password);

            if (empty($error)) {
                if ($this->userManager->emailExists($email)) {
                    $error['email'] = "Email already exists";
                } else {
                    if ($this->userManager->registeredByAdmin($fname, $lname, $email, $password)) {
                        $message = "Account created successfully";
                        $encodedMessage = urlencode($message);
                        header("Location: /app/Views/admin/customers.php?message=$encodedMessage");
                        exit();
                    } else {
                        $error['general'] = "Something went wrong. Please try again.";
                    }
                }
            }

            // Render view with errors if validation fails
            return $error;
        }
    }

    private function sanitize($data)
    {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    private function validate($fname, $lname, $email, $password)
    {
        $error = [];

        // Name Validation
        if (empty($fname)) {
            $error['first-name'] = "Enter your First name";
        }

        if (empty($lname)) {
            $error['last-name'] = "Enter your Last name";
        }

        // Email Validation
        if (empty($email)) {
            $error['email'] = "Enter your email";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error['email'] = "Enter a valid email";
        }

        // Password Validation
        if (empty($password)) {
            $error['password'] = "Enter your password";
        } elseif (strlen($password) < 6) {
            $error['password'] = "Password must be at least 6 characters";
        }

        return $error;
    }
}
