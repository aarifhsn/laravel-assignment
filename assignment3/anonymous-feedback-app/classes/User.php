<?php

class User
{
    private $users = [];
    public $userFiles = 'data/Users.json';

    public function __construct()
    {
        $this->users = json_decode(file_get_contents($this->userFiles), true);
    }
    public function userExists($username)
    {
        // check if user exists
        foreach ($this->users as $user) {
            if ($user['username'] === $username) {
                return true;
            }
        }
        return false;
    }
    public function register($username, $password, $email)
    {
        if ($this->userExists($username)) {
            return false;
        } else {
            $this->users[] = [
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'email' => $email
            ];
            return true;
        }
    }
}

class UserManagement
{
    public function __construct()
    {
        $this->userDirectory();
    }

    private function userDirectory()
    {
        if (!is_dir('data')) {
            mkdir('data', 0777, true);
        }
    }
}
