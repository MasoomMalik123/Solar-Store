<?php
// PHPMailer SMTP wrapper
// Install PHPMailer via Composer in the project root:
// composer require phpmailer/phpmailer

function send_mail_smtp($to, $subject, $body, $replyTo = null){
    // Default return structure
    $result = ['success' => false, 'message' => ''];

    // Mailer config - override by creating components/smtp_config.php returning $config array
    $config = [
        'host' => 'smtp.example.com',
        'username' => 'smtp-user@example.com',
        'password' => 'smtp-password',
        'port' => 587,
        'secure' => 'tls', // 'ssl' or 'tls'
        'from' => 'no-reply@solarstore.local',
        'from_name' => 'Solar Store',
    ];

    // Load override config if present
    if(file_exists(__DIR__ . '/smtp_config.php')){
        $cfg = include __DIR__ . '/smtp_config.php';
        if(is_array($cfg)){
            $config = array_merge($config, $cfg);
        }
    }

    // Try to use PHPMailer if available
    if(file_exists(__DIR__ . '/../vendor/autoload.php')){
        require_once __DIR__ . '/../vendor/autoload.php';
        try{
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = $config['secure'];
            $mail->Port = $config['port'];

            $mail->setFrom($config['from'], $config['from_name']);
            $mail->addAddress($to);
            if($replyTo){ $mail->addReplyTo($replyTo); }

            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            $result['success'] = true;
            $result['message'] = 'Message sent via PHPMailer SMTP.';
            return $result;
        }catch(Exception $e){
            $result['message'] = 'PHPMailer error: ' . $mail->ErrorInfo;
            // fall through to fallback mail()
        }
    }

    // Fallback to PHP mail()
    $headers = "From: " . $config['from'] . "\r\n";
    if($replyTo) $headers .= "Reply-To: $replyTo\r\n";
    if(mail($to, $subject, $body, $headers)){
        $result['success'] = true;
        $result['message'] = 'Message sent via PHP mail() fallback.';
    }else{
        $result['message'] = 'Failed to send via PHP mail() fallback. Configure SMTP or install PHPMailer via Composer.';
    }

    return $result;
}
