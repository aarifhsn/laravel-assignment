<?php

//Problem 1:

// Given a list of integers, find the minimum of their absolute values. 
// 'Absolute values' means the non-negative value without regard to its sign. For example, the Absolute value of 123 is 123, and the Absolute value of -123 is also 123. 

function minAbsValue($numbers)
{
    // assume the min value is the first number
    $minValue = $numbers[0];

    foreach ($numbers as $number) {
        // calculate the abs value
        $absValue = $number < 0 ? -$number : $number;

        if ($absValue < $minValue) {
            $minValue = $absValue;
        }
    }
    return $minValue;
}

$numbers = [3, 20, -9, -5, 13, 40, -5, 6, -4, -2];
echo minAbsValue($numbers) . "\n";
echo "<br />";


//using PHP built-in function
// $array = [3, 4, 22, -10, -4, 34, -2];
// $absValue = array_map('abs', $array);
// echo "Min Abs Value: " . min($absValue) . "<br />";




// Problem 2:
// Given a few paragraphs in a file, read the file and count how many words are there. 

function countWords($filename)
{
    $file = fopen($filename, 'r');
    // Check if the file is available 
    if (!$file) {
        echo "Unable to open the file";
        return 0;
    }

    $wordCount = 0;
    // fgets() - gets line from file pointer. Explode() - split a line into words
    while (($para = fgets($file)) == true) {
        if (!empty($para)) {
            $words = explode(' ', $para);

            // increment words
            $wordCount = $wordCount + count($words);
        }
    }
    fclose($file);
    return $wordCount;
}

// Get the file
$filename = './content.txt';

echo "Number of words: " . countWords($filename) . "\n";
echo "<br />";

//using PHP built-in function
// str_word_count($str)




// Problem 3:
// Given a sentence, keep the order of the words same, but reverse the characters of each word.  For example, if the given sentence is: ‘I love programming’  The result should be: ‘I evol gnimmargorp’

// 1. reverse sentence
function reverseString($str)
{

    $length = strlen($str);
    $reversed = '';
    for ($i = $length - 1; $i >= 0; $i--) {
        $reversed .= $str[$i];
    }
    return $reversed; //  mroftalp tnellecxe na si seraC evitcaretnI
}

// 2. split the sentence into words
function reverseSentence($sentence)
{
    $words = [];
    $word = '';

    for ($i = 0; isset($sentence[$i]); $i++) {
        if ($sentence[$i] === ' ') {
            if ($word !== '') {
                $words[] = $word;
                $word = '';
            }
        } else {
            $word .= $sentence[$i];
        }
    }

    if ($word !== '') {
        $words[] = $word;
    }
    // echo $word; // platformInteractiveCaresisanexcellent 

    // 3. reverse all words and collect them
    $reversedWords = [];
    foreach ($words as $word) {
        $reversedWords[] = reverseString($word);
    }


    // 4. join the reversed words to make it a sentence.
    $reversedSentence = '';
    foreach ($reversedWords as $word) {
        if ($reversedSentence !== '') {
            $reversedSentence .= ' ';
        }
        $reversedSentence .= $word;
    }
    return $reversedSentence;
}

$sentence = "Interactive Cares is an excellent platform .";

echo "Main sentence: " . $sentence . "\n";
echo "Reversed Sentence: " . reverseSentence($sentence) . "\n";
echo "<br />";

//using PHP built-in function
// $string = 'I LOVE PROGRAMMING';
// strrev(implode(' ', array_reverse(explode(' ', $string))));




// Problem 4:
// Print the following pattern based on the given number n (can be any number). 

function pyramidPattarn($level)
{
    for ($i = 0; $i < $level; $i++) {
        // print spaces
        for ($j = $level - $i; $j > 0; $j--) {
            echo "&nbsp;&nbsp;";
        }
        for ($k = 1; $k <= 2 * $i - 1; $k++) {
            echo "*";
        }
        echo "<br>";
    }
}

// Set the number of levels for the pyramid
$level = 5;
pyramidPattarn($level);
echo "<br />";


//using PHP built-in function

// function pyramidPattarn($level, $starIcon = '*')
// {
//     for ($i = 1; $i <= $level; $i++) {
//         $spaces = str_repeat('&nbsp;', $level - $i);
//         $stars = str_repeat($starIcon, 2 * $i - 1);
//         echo $spaces . $stars . "<br>";
//     }
// }



// Problem 5:
// Given an integer n, find the sum of the digits of the integer.

$n = 453782;

function intSum($n)
{
    $sum = 0;

    // if the number is negative, make it positive
    if ($n < 0) {
        $n = -1 * $n;
    }

    // repeat the process until the number becomes zero.
    while ($n > 0) {
        // get the last digit and add it to the sum.
        $sum += $n % 10;

        // remove the last digit
        $n = ($n - ($n % 10)) / 10;
    }
    return $sum;
}

echo intSum($n);
