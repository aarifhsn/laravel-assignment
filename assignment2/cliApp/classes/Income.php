<?php

namespace cliApp\classes;

require_once "Transaction.php";
class Income extends Transaction
{
    public function __construct($amount, $category)
    {
        parent::__construct($amount, $category);
    }
}
