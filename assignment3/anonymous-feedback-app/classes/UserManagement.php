<?php

class UserManagement
{
    private $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function read()
    {
        if (!file_exists($this->filePath)) {
            return [];
        } else {
            $file = fopen($this->filePath, 'r');
            $data = fread($file, filesize($this->filePath));
            fclose($file);
            return json_decode($data, true);
        }
    }

    public function write($data)
    {
        $file = fopen($this->filePath, 'w');
        fwrite($file, json_encode($data));
        fclose($file);
    }
}
