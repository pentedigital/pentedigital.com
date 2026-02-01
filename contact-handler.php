<?php
header('Content-Type: application/json');

// Configuration
$to_email = 'hello@pentedigital.com';
$subject_prefix = '[Pente Digital Website] ';

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get and sanitize form data
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING) ?? '';
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '';
$company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_STRING) ?? '';
$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING) ?? '';

// Validate required fields
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

if (empty($message)) {
    $errors[] = 'Message is required';
}

// Honeypot check (anti-spam)
if (!empty($_POST['website'])) {
    // Bot detected
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid submission']);
    exit;
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Prepare email
$subject = $subject_prefix . 'New Contact from ' . $name;

$email_body = "New contact form submission from Pente Digital website:\n\n";
$email_body .= "Name: $name\n";
$email_body .= "Email: $email\n";
$email_body .= "Company: " . ($company ?: 'Not provided') . "\n";
$email_body .= "Message:\n$message\n\n";
$email_body .= "---\n";
$email_body .= "Submitted: " . date('Y-m-d H:i:s') . "\n";
$email_body .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";

$headers = [
    'From: noreply@pentedigital.com',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8'
];

// Send email
$mail_sent = mail($to_email, $subject, $email_body, implode("\r\n", $headers));

if ($mail_sent) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message. We will get back to you soon.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send message. Please try again or email us directly.'
    ]);
}
?>
