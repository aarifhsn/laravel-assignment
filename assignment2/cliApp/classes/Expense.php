<?php
require_once "Transaction.php";
class Expense extends Transaction
{
    public function __construct($amount, $category)
    {
        parent::__construct($amount, $category);
    }
}
