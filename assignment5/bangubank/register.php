<?php

require_once __DIR__ . '/vendor/autoload.php';

use Bangubank\Models\User;

// load configuration
$config = require __DIR__ . '/app/config/config.php';
$db_setup = require __DIR__ . '/app/config/db_setup.php';
$filePath = $config['filePath'];

// Initialize User and AdminUser objects
$user = new User($config);

$error = [];
$name = $email = $password = '';

function sanitize($data)
{
  return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

  // Name Validation
  if (empty($_POST['name'])) {
    $error['name'] = "Enter your name";
  } else {
    $name = sanitize($_POST['name']);
  }
  // Email Validation
  if (empty($_POST['email'])) {
    $error['email'] = "Enter your email";
  } else {
    $email = sanitize($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error['email'] = "Enter a valid email";
    }
  }
  // Password Validation
  if (empty($_POST['password'])) {
    $error['password'] = "Enter your password";
  } elseif (strlen($_POST['password']) < 6) {
    $error['password'] = "Password must be at least 6 characters";
  } else {
    $password = sanitize($_POST['password']);
  }

  if (empty($error)) {
    if ($config['storage'] === 'file') {
      if ($user->emailExists($email)) {
        $error['email'] = "Email already exists";
      } else {
        if ($user->register($name, $email, $password)) {
          $message = "Account created successfully";
          $encodedMessage = urlencode($message);
          header("Location: login.php?message=$encodedMessage");
          exit();
        } else {
          $error['general'] = "Something went wrong. Please try again.";
        }
      }
    } elseif ($config['storage'] === 'database' && isset($pdo)) {

      $query = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";

      $stmt = $pdo->prepare($query);

      $param = [
        ':name' => $name,
        ':email' => $email,
        ':password' => $password
      ];
      if ($stmt->execute($param)) {
        header("Location: login.php?message=Account created successfully");
        exit();
      } else {
        $auth_error = "Something went wrong. Please try again.";
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

  <title>Create A New Account</title>
</head>

<body class="h-full bg-slate-100">
  <div class="flex flex-col justify-center min-h-full py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <h2 class="mt-6 text-2xl font-bold leading-9 tracking-tight text-center text-gray-900">
        Create A New Account
      </h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
      <div class="px-6 py-12 bg-white shadow sm:rounded-lg sm:px-12">
        <?php
        if (!empty($error)) : ?>
          <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
            <ul>
              <?php foreach ($error as $err) : ?>
                <li><?php echo $err; ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
        <?php if (isset($auth_error) && !empty($auth_error)) : ?>
          <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
            <?php echo $auth_error; ?>
          </div>
        <?php endif; ?>
        <?php if (isset($message) && !empty($message)) : ?>
          <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
            <?php echo $message; ?>
          </div>
        <?php endif; ?>
        <form class="space-y-6" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
          <div>
            <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Name</label>
            <div class="mt-2">
              <input id="name" name="name" type="text" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6 p-2" />
            </div>
            <?php if (isset($error['name'])) : ?>
              <p class="text-red-500 text-sm mt-1">
                <?php echo $error['name']; ?>
              </p>
            <?php endif; ?>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
            <div class="mt-2">
              <input id="email" name="email" type="email" autocomplete="email" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6 p-2" />
            </div>
            <?php if (isset($error['email'])) : ?>
              <p class="text-red-500 text-sm mt-1">
                <?php echo $error['email']; ?>
              </p>
            <?php endif; ?>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
            <div class="mt-2">
              <input id="password" name="password" type="password" autocomplete="current-password" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6 p-2" />
            </div>
            <?php if (isset($error['password'])) : ?>
              <p class="text-red-500 text-sm mt-1">
                <?php echo $error['password']; ?>
              </p>
            <?php endif; ?>
          </div>

          <div>
            <button type="submit" class="flex w-full justify-center rounded-md bg-emerald-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
              Register
            </button>
          </div>
        </form>
      </div>

      <p class="mt-10 text-sm text-center text-gray-500">
        Already a customer?
        <a href="./login.php" class="font-semibold leading-6 text-emerald-600 hover:text-emerald-500">Sign-in</a>
      </p>
    </div>
  </div>
</body>

</html>