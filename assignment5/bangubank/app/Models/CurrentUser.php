<?php

namespace Bangubank;

class CurrentUser
{
    private $sessionManager;
    private $userManager;

    public function __construct(SessionManager $sessionManager, UserManager $userManager)
    {
        $this->sessionManager = $sessionManager;
        $this->userManager = $userManager;
    }

    public function getLoggedInUser()
    {
        if ($this->sessionManager->isLoggedIn()) {
            return $this->userManager->getUserByEmail($this->sessionManager->getEmail());
        }
        return null;
    }
}
