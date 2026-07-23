<?php
/**
 * Professional email validation and helper utilities
 */

/**
 * Validates email format according to professional requirements and checks DNS.
 * 
 * @param string $email The email to validate
 * @param string &$errorMsg Output parameter for user-friendly error message
 * @return bool True if valid, false otherwise
 */
function validateEmailProfessional($email, &$errorMsg)
{
    // 6. No spaces are allowed anywhere in the email.
    if (strpos($email, ' ') !== false) {
        $errorMsg = 'Please enter a valid email address.';
        return false;
    }

    $email = trim($email);
    if ($email === '') {
        $errorMsg = 'Please enter a valid email address.';
        return false;
    }

    // 3. Email must contain exactly one "@" symbol.
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        $errorMsg = 'Please enter a valid email address.';
        return false;
    }

    $localPart = $parts[0];
    $domainPart = $parts[1];

    // 4. Local part cannot be empty.
    // 5. Domain part cannot be empty.
    if ($localPart === '' || $domainPart === '') {
        $errorMsg = 'Please enter a valid email address.';
        return false;
    }

    // 7. Do not allow consecutive dots (..).
    if (strpos($email, '..') !== false) {
        $errorMsg = 'Please enter a valid email address.';
        return false;
    }

    // 8. Do not allow the email to start or end with a dot.
    if (str_starts_with($localPart, '.') || str_ends_with($localPart, '.')) {
        $errorMsg = 'Please enter a valid email address.';
        return false;
    }

    // 8 & 9. Do not allow the domain to start or end with a hyphen or dot.
    if (str_starts_with($domainPart, '.') || str_ends_with($domainPart, '.') ||
        str_starts_with($domainPart, '-') || str_ends_with($domainPart, '-')) {
        $errorMsg = 'Please enter a valid email address.';
        return false;
    }

    // 17. Follow RFC 5322 email formatting standards using filter_var validation.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Please enter a valid email address.';
        return false;
    }

    // 10. Require a valid top-level domain (TLD) and format check.
    $domainParts = explode('.', $domainPart);
    if (count($domainParts) < 2) {
        $errorMsg = 'Please enter a valid email address.';
        return false;
    }

    $tld = end($domainParts);

    // Reject common typo extensions (coom, comm, commmmm, coo, cm, om, etc.)
    $invalidTlds = ['coom', 'comm', 'commmmm', 'coo', 'cm', 'om'];
    if (in_array(strtolower($tld), $invalidTlds, true)) {
        $errorMsg = 'Please enter a valid email address.';
        return false;
    }

    // Require standard length for TLDs
    if (strlen($tld) < 2 || strlen($tld) > 6) {
        $errorMsg = 'Please enter a valid email address.';
        return false;
    }

    if (!preg_match('/^[a-zA-Z]{2,6}$/', $tld)) {
        $errorMsg = 'Please enter a valid email address.';
        return false;
    }

    // 11. Reject fake or malformed domains / typos.
    $domainPartLower = strtolower($domainPart);
    $typoDomains = ['gamil.com', 'gmail.coom', 'gmail.comm', 'gmail.commmmm', 'yahoo.comm', 'hotmail.coom'];
    if (in_array($domainPartLower, $typoDomains, true)) {
        $errorMsg = 'Please enter a valid email address.';
        return false;
    }

    // 12. Verify that the domain actually exists using DNS MX or A record lookup.
    if (!checkdnsrr($domainPart, 'MX') && !checkdnsrr($domainPart, 'A')) {
        $errorMsg = 'Please enter a valid email address.';
        return false;
    }

    return true;
}

/**
 * Logs the verification email locally and attempts to send via PHP mail.
 * 
 * @param string $email The destination email address
 * @param string $token The verification token
 * @return bool True if successful
 */
function sendVerificationEmailLocal($email, $token)
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $verifyUrl = $protocol . '://' . $host . '/Major-project/frontend/verify.php?token=' . urlencode($token);

    $subject = "Verify Your Account - Nepal Tour and Travel";
    $message = "Hello,\n\nThank you for signing up at Nepal Tour and Travel.\n\n";
    $message .= "Please click the link below to verify your email address and activate your account:\n";
    $message .= $verifyUrl . "\n\n";
    $message .= "Best regards,\nNepal Tour and Travel Team";

    $headers = "From: no-reply@nepaltravel.com\r\n";
    $headers .= "Reply-To: no-reply@nepaltravel.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Log to file for easy local recovery and verification testing
    $logDir = __DIR__;
    $logFile = $logDir . '/verification_emails.log';
    
    $logContent = "================================================\n";
    $logContent .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
    $logContent .= "To: " . $email . "\n";
    $logContent .= "Subject: " . $subject . "\n";
    $logContent .= "Message:\n" . $message . "\n";
    $logContent .= "================================================\n\n";

    file_put_contents($logFile, $logContent, FILE_APPEND);

    // Also attempt real mail delivery
    @mail($email, $subject, $message, $headers);

    return true;
}
