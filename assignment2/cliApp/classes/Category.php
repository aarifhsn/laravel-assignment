<?php

class Category
{
    private $categories = [];
    private $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->load();
    }

    public function addCategory($category)
    {
        if (!in_array($category, $this->categories)) {
            $this->categories[] = $category;
            $this->save();
        }
    }

    public function getCategories()
    {
        return $this->categories;
    }

    private function load()
    {
        if (file_exists($this->filename)) {
            $this->categories = json_decode(file_get_contents($this->filename), true) ?? [];
        }
    }

    private function save()
    {
        file_put_contents($this->filename, json_encode($this->categories));
    }
}
