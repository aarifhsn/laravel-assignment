<?php

function sanitize(string $data): string
{
    // scape all html characters, and remove leading, slashes and trailing whitespace
    return htmlspecialchars(stripslashes(trim($data)));
}
