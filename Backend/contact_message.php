<?php
require_once __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('contact.php');
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
    header('Location: contact.php?message=invalid');
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
    $stmt->execute([mb_substr($name, 0, 120), mb_substr($email, 0, 190), mb_substr($subject, 0, 190) ?: null, $message]);
    header('Location: contact.php?message=sent');
} catch (Throwable $e) {
    header('Location: contact.php?message=error');
}
exit;
