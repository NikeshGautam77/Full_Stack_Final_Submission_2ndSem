<?php
/**
 * CAPTCHA Security Module
 * Provides functions to generate and verify math-based CAPTCHAs
 */

// Generate a random math CAPTCHA
function generateCaptcha() {
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $operation = ['+', '-', '*'][array_rand(['+', '-', '*'])];
    
    switch ($operation) {
        case '+':
            $answer = $num1 + $num2;
            break;
        case '-':
            $answer = $num1 - $num2;
            break;
        case '*':
            $answer = $num1 * $num2;
            break;
    }
    
    $question = "$num1 $operation $num2 = ?";
    
    // Store in session
    $_SESSION['captcha_question'] = $question;
    $_SESSION['captcha_answer'] = (string)$answer;
    
    return $question;
}

// Verify the user's CAPTCHA response
function verifyCaptcha($user_answer) {
    if (!isset($_SESSION['captcha_answer'])) {
        return false;
    }
    
    $user_answer = trim(sanitizeInput($user_answer));
    $correct_answer = $_SESSION['captcha_answer'];
    
    // Clear the CAPTCHA from session after verification attempt
    unset($_SESSION['captcha_question']);
    unset($_SESSION['captcha_answer']);
    
    return $user_answer === $correct_answer;
}

// Sanitize user input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validate username (alphanumeric and underscore only)
function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

// Validate password (minimum 6 characters)
function validatePassword($password) {
    return strlen($password) >= 6;
}

// Generate CSRF token
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
?>
