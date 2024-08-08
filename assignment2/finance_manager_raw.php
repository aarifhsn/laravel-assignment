<?php

// Define file paths
$incomeFile = 'data/incomes.json';
$expenseFile = 'data/expenses.json';
$categoryFile = 'data/categories.json';

// Load data from JSON files
function loadData($filename)
{
    if (file_exists($filename)) {
        return json_decode(file_get_contents($filename), true) ?: [];
    }
    return [];
}

// Save data to JSON files
function saveData($filename, $data)
{
    file_put_contents($filename, json_encode($data));
}

// Get user input from the command line
function getUserInput($prompt)
{
    echo $prompt;
    return trim(fgets(STDIN));
}

// Get valid amount from the user
function getValidAmount($prompt)
{
    do {
        $input = getUserInput($prompt);
        if (empty($input)) {
            echo "Please enter a valid non-zero amount.\n";
            continue;
        }
        $amount = (float)$input;
        if ($amount <= 0) {
            echo "Please enter a valid non-zero amount.\n";
        }
    } while (empty($input) || $amount <= 0);
    return $amount;
}

// Add a category
function addCategory(&$categories, $category)
{
    if (!in_array($category, $categories)) {
        $categories[] = $category;
        saveData('data/categories.json', $categories);
    }
}

// Add income
function addIncome(&$incomes, &$categories)
{
    $amount = getValidAmount("Enter income amount: ");
    $category = getUserInput("Enter income category: ");
    addCategory($categories, $category);
    $incomes[] = ['amount' => $amount, 'category' => $category];
    saveData('data/incomes.json', $incomes);
    echo "Income added successfully.\n";
}

// Add expense
function addExpense(&$expenses, &$categories)
{
    $amount = getValidAmount("Enter expense amount: ");
    $category = getUserInput("Enter expense category: ");
    addCategory($categories, $category);
    $expenses[] = ['amount' => $amount, 'category' => $category];
    saveData('data/expenses.json', $expenses);
    echo "Expense added successfully.\n";
}

// View incomes
function viewIncomes($incomes)
{
    echo "Incomes:\n";
    foreach ($incomes as $income) {
        echo "Amount: {$income['amount']}, Category: {$income['category']}\n";
    }
}

// View expenses
function viewExpenses($expenses)
{
    echo "Expenses:\n";
    foreach ($expenses as $expense) {
        echo "Amount: {$expense['amount']}, Category: {$expense['category']}\n";
    }
}

// View savings
function viewSavings($incomes, $expenses)
{
    $totalIncome = array_sum(array_column($incomes, 'amount'));
    $totalExpense = array_sum(array_column($expenses, 'amount'));
    $savings = $totalIncome - $totalExpense;
    echo "Savings: $savings\n";
}

// View categories
function viewCategories($categories)
{
    echo "Categories:\n";
    foreach ($categories as $category) {
        echo "$category\n";
    }
}

// Load data from files
$incomes = loadData($incomeFile);
$expenses = loadData($expenseFile);
$categories = loadData($categoryFile);

do {
    echo "\n1. Add income\n";
    echo "2. Add expense\n";
    echo "3. View incomes\n";
    echo "4. View expenses\n";
    echo "5. View savings\n";
    echo "6. View categories\n";
    echo "7. Exit\n";
    $option = getUserInput("Enter your option: ");

    switch ($option) {
        case 1:
            addIncome($incomes, $categories);
            break;

        case 2:
            addExpense($expenses, $categories);
            break;

        case 3:
            viewIncomes($incomes);
            break;

        case 4:
            viewExpenses($expenses);
            break;

        case 5:
            viewSavings($incomes, $expenses);
            break;

        case 6:
            viewCategories($categories);
            break;

        case 7:
            echo "Exiting...\n";
            exit;

        default:
            echo "Invalid option. Please try again.\n";
            break;
    }
} while (true);
