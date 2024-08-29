<?php

require_once __DIR__ . '/vendor/autoload.php';

use Bangubank\Models\AdminUser;
use Bangubank\Models\User;

//Start the session.
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// load configuration
$config = require __DIR__ . '/app/config/config.php';
$db_setup = require __DIR__ . '/app/config/db_setup.php';
$filePath = $config['filePath'];

// Initialize User and AdminUser objects
$user = new User($config);
$admin_user = new AdminUser($filePath);


if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $email = $_POST['email'];
  $password = $_POST['password'];

  if ($config['storage'] === 'database' && isset($pdo)) {
    $query = 'SELECT * FROM users WHERE email = :email';

    $stmt = $pdo->prepare($query);

    if ($stmt->execute([':email' => $email])) {
      $userData = $stmt->fetch();
      if ($userData) {
        $_SESSION['email'] = $userData['email'];
        $_SESSION['role'] = $userData['role'];

        // Redirect to the appropriate page based on user role
        header('Location: ' . ($userData['role'] === 'admin' ? 'admin/customers.php' : 'customer/dashboard.php'));
        exit;
      } else {
        $loginError = "Invalid email or password.";
      }
    }
  } elseif ($config['storage'] === 'file') {

    // Check if the user is an admin
    $admin = $admin_user->getAdminUser($email);

    if ($admin) {
      if (password_verify($password, $admin['password'])) {
        $_SESSION['email'] = $email;
        $_SESSION['password'] = $password;
        $_SESSION['role'] = 'admin';
        header('Location: admin/customers.php');
        exit;
      } else {
        $loginError = "Invalid password for admin.";
      }
    } else {
      // Check if the user is a customer
      if ($user->login($email, $password)) {
        header('Location: customer/dashboard.php');
        exit;
      } else {
        $loginError = "Invalid email or password.";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html class="h-full bg-white" lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <style>
    * {
      font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont,
        'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans',
        'Helvetica Neue', sans-serif;
    }
  </style>

  <title>Sign-In To Your Account</title>
</head>

<body class="h-full bg-slate-100">
  <div class="flex flex-col justify-center min-h-full py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="my-8">
        <?php
        if (isset($_GET['message'])) { ?>
          <div class="redirect_message text-center py-6 text-green-600 font-bold">
            <?php
            $message = urldecode($_GET['message']);
            echo "<p>{$message}</p>";
            ?>
          </div>
        <?php } ?>
      </div>
      <h2 class="mt-6 text-2xl font-bold leading-9 tracking-tight text-center text-gray-900">
        Sign In To Your Account
      </h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
      <div class="px-6 py-12 bg-white shadow sm:rounded-lg sm:px-12">
        <form class="space-y-6" action="#" method="POST">
          <?php if (isset($loginError)) { ?>
            <div class="redirect_message text-center text-red-600 font-bold">
              <?php
              echo "<p>{$loginError}</p>";
              ?>
            </div>
          <?php } ?>
          <div>
            <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
            <div class="mt-2">
              <input id="email" name="email" type="email" autocomplete="email" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 p-2 sm:text-sm sm:leading-6" />
            </div>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
            <div class="mt-2">
              <input id="password" name="password" type="password" autocomplete="current-password" required class="block w-full p-2 text-gray-900 border-0 rounded-md shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6" />
            </div>
          </div>

          <div>
            <button type="submit" class="flex w-full justify-center rounded-md bg-emerald-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
              Sign in
            </button>
          </div>
        </form>
      </div>

      <p class="mt-10 text-sm text-center text-gray-500">
        Don't have an account?
        <a href="./register.php" class="font-semibold leading-6 text-emerald-600 hover:text-emerald-500">Register</a>
      </p>
    </div>
  </div>
</body>

</html>