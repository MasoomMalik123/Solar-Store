<?php
// Copy this file to components/smtp_config.php and set real SMTP credentials.
return [
    'host' => 'smtp.example.com',
    'username' => 'user@example.com',
    'password' => 'your-smtp-password',
    'port' => 587,
    'secure' => 'tls',
    'from' => 'no-reply@yourdomain.com',
    'from_name' => 'Solar Store',
];
