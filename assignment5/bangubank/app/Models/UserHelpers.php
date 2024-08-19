<?php

namespace Bangubank;

class UserHelpers
{
    public function getFirstChar($string)
    {
        preg_match_all('/\b\w/', $string, $matches);
        return implode('', $matches[0]);
    }
}
