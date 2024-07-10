<?php

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['feedback'])) {

    $feedback = $_POST['feedback'];

    // store feedback in a file
    $fileName = 'feedback.json';

    // check if the file exists and read its content
    if (file_exists($fileName)) {
        $fileContent = file_get_contents($fileName);
        $feedbacks = json_decode($fileContent, true);
    } else {
        $feedbacks = array();
    }
    // add feedback to the array
    $newFeedback = [
        'feedback' => $feedback
    ];
    $feedbacks[] = $newFeedback;

    // save the updated content
    $fileContent = json_encode($feedbacks, JSON_PRETTY_PRINT);
    file_put_contents($fileName, $fileContent);

    // redirect back to feedback page with success message
    header("Location: feedback-success.php?success=true");
    exit();
}
