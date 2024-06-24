<?php


namespace cliApp\classes;

class FinanceManager
{
    private $incomes = [];
    private $expenses = [];
    private $incomeFile = 'data/incomes.json';
    private $expenseFile = 'data/expenses.json';
    private $categoryManager;

    public function __construct($categoryManager)
    {
        $this->categoryManager = $categoryManager;
        $this->dataDirectory();
        $this->load();
    }

    public function addIncome($income)
    {
        $this->incomes[] = $income;
        $this->save();
    }

    public function addExpense($expense)
    {
        $this->expenses[] = $expense;
        $this->save();
    }

    public function getIncomes()
    {
        return $this->incomes;
    }

    public function getExpenses()
    {
        return $this->expenses;
    }

    public function getSavings()
    {
        $totalIncome = array_reduce($this->incomes, fn ($sum, $income) => $sum + $income->amount, 0);
        $totalExpense = array_reduce($this->expenses, fn ($sum, $expense) => $sum + $expense->amount, 0);
        return $totalIncome - $totalExpense;
    }

    private function dataDirectory()
    {
        if (!is_dir('data')) {
            mkdir('data', 0777, true);
        }
    }

    private function load()
    {
        if (file_exists($this->incomeFile)) {
            // Read the file contents and decode the JSON data into an array
            $incomeData = json_decode(file_get_contents($this->incomeFile), true) ?? [];

            // Map the data to Income objects
            $this->incomes = array_map(
                fn ($data) => new Income($data['amount'], $data['category']),
                $incomeData
            );
        }
        if (file_exists($this->expenseFile)) {
            // Read the file contents and decode the JSON data into an array
            $expenseData = json_decode(file_get_contents($this->expenseFile), true) ?? [];

            // Map the data to Expense objects
            $this->expenses = array_map(
                fn ($data) => new Expense($data['amount'], $data['category']),
                $expenseData
            );
        }
    }

    private function save()
    {
        // Save the incomes to the income file
        file_put_contents(
            $this->incomeFile,
            json_encode(
                array_map(fn ($income) => get_object_vars($income), $this->incomes)
            )
        );

        // Save the expenses to the expense file
        file_put_contents(
            $this->expenseFile,
            json_encode(
                array_map(fn ($expense) => get_object_vars($expense), $this->expenses)
            )
        );
    }
}
