<?php

class FileManager
{
    private $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
        $this->createDirectoryIfNotExists();
    }

    private function createDirectoryIfNotExists()
    {
        if (!is_dir(dirname($this->filePath))) {
            if (!mkdir(dirname($this->filePath), 0777, true)) {
                die('Failed to create directories...');
            }
        }
    }


    public function read()
    {
        if (!file_exists($this->filePath)) {
            return [];
        }
        $data = file_get_contents($this->filePath);
        if ($data === false) {
            die('Failed to read file...');
        }
        return json_decode($data, true);
    }

    public function write($data)
    {
        $json_data = json_encode($data, JSON_PRETTY_PRINT);
        if (file_put_contents($this->filePath, $json_data) === false) {
            die('Failed to write file...');
        };
    }
}
