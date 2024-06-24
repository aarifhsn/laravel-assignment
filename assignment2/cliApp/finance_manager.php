<?php

//require_once 'classes/Transaction.php';
require_once 'classes/Income.php';
require_once 'classes/Expense.php';
require_once 'classes/Category.php';
require_once 'classes/FinanceManager.php';

use  cliApp\classes\Income;
use  cliApp\classes\Expense;
use  cliApp\classes\Category;
use  cliApp\classes\FinanceManager;
use  cliApp\classes\Transaction;

// Function to get user input
function getUserInput($prompt)
{
    echo $prompt;
    return trim(fgets(STDIN));
}


$categoryManager = new Category('data/categories.json');
$financeManager = new FinanceManager($categoryManager);

// validate amount input when its less then zero or empty
function validAmount()
{
    do {
        $amount = (float)getUserInput("Enter amount: ");
        if ($amount <= 0 || empty($amount)) {
            echo "PLease enter a valid amount.\n";
        }
    } while ($amount <= 0);
    return $amount;
}

while (true) {
    echo "\n1. Add income\n2. Add expense\n3. View incomes\n4. View expenses\n5. View savings\n6. View categories\n7. Exit\n\nEnter your option: ";
    $option = getUserInput("");

    switch ($option) {
        case 1:
            $amount = validAmount();
            $category = getUserInput("Enter income category: ");
            $categoryManager->addCategory($category);
            $financeManager->addIncome(new Income($amount, $category));
            echo "Income added successfully.\n";
            break;

        case 2:
            $amount = validAmount();
            $category = getUserInput("Enter expense category: ");
            $categoryManager->addCategory($category);
            $financeManager->addExpense(new Expense($amount, $category));
            echo "Expense added successfully.\n";
            break;

        case 3:
            $incomes = $financeManager->getIncomes();
            if (count($incomes) > 0) {
                echo "Incomes:\n";
                foreach ($incomes as $income) {
                    echo "{$income->category}: \${$income->amount}\n";
                }
            } else {
                echo "No incomes found.\n";
            }
            break;

        case 4:
            $expenses = $financeManager->getExpenses();
            if (count($expenses) > 0) {
                echo "Expenses:\n";
                foreach ($expenses as $expense) {
                    echo "{$expense->category}: \${$expense->amount}\n";
                }
            } else {
                echo "No expenses found.\n";
            }
            break;

        case 5:
            echo "Savings: \$" . $financeManager->getSavings() . "\n";
            break;

        case 6:
            $categories = $categoryManager->getCategories();
            if (count($categories) > 0) {
                echo "Categories:\n";
                foreach ($categories as $category) {
                    echo "$category\n";
                }
            } else {
                echo "No categories found.\n";
            }
            break;

        case 7:
            exit("Goodbye!\n");

        default:
            echo "Invalid option. Please try again.\n";
            break;
    }
}
