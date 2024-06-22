<?php

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
        $this->ensureDataDirectoryExists();
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

    private function ensureDataDirectoryExists()
    {
        if (!is_dir('data')) {
            mkdir('data', 0777, true);
        }
    }

    private function load()
    {
        if (file_exists($this->incomeFile)) {
            $this->incomes = array_map(fn ($data) => new Income($data['amount'], $data['category']), json_decode(file_get_contents($this->incomeFile), true) ?? []);
        }
        if (file_exists($this->expenseFile)) {
            $this->expenses = array_map(fn ($data) => new Expense($data['amount'], $data['category']), json_decode(file_get_contents($this->expenseFile), true) ?? []);
        }
    }

    private function save()
    {
        file_put_contents($this->incomeFile, json_encode(array_map(fn ($income) => get_object_vars($income), $this->incomes)));
        file_put_contents($this->expenseFile, json_encode(array_map(fn ($expense) => get_object_vars($expense), $this->expenses)));
    }
}
