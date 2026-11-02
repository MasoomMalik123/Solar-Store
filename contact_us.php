<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

include 'components/wishlist_cart.php';

// Change this to your real recipient email
$site_email = 'admin@solarstore.local';

// Mailer wrapper (uses PHPMailer if installed via Composer)
include_once 'components/mailer.php';

$sent = false;
$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $subject = trim($_POST['subject'] ?? 'Solar Solution Request');

    if(empty($name) || empty($email) || empty($message)){
        $error = 'Please fill in name, email and message.';
    }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = 'Please provide a valid email address.';
    }else{
        $body = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message\n";
        $headers = "From: $site_email\r\n";
        $headers .= "Reply-To: $email\r\n";

        // Try to send email using PHPMailer+SMTP (if installed) or fallback to mail()
        $sendResult = send_mail_smtp($site_email, $subject, $body, $email);
        // log result
        $logLine = date('Y-m-d H:i:s') . " | to:$site_email | from:$email | subject:" . addslashes($subject) . " | result:" . json_encode($sendResult) . "\n";
        @file_put_contents(__DIR__ . '/components/mail_log.txt', $logLine, FILE_APPEND | LOCK_EX);

        if(isset($sendResult['success']) && $sendResult['success']){
            $sent = true;
        }else{
            $error = 'Failed to send email: ' . ($sendResult['message'] ?? 'unknown error');
        }

        // Optional: store in messages table if exists
        try{
            $stmt = $conn->prepare("INSERT INTO messages (name, email, phone, subject, message, user_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message, $user_id]);
        }catch(Exception $e){
            // ignore if messages table doesn't exist
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Contact Us</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section style="padding:3rem; max-width:800px; margin:4rem auto; background:var(--white); border-radius:.6rem; box-shadow:var(--box-shadow);">
    <h1 class="heading">Contact Us</h1>
    <!-- status modal (hidden, shown via JS) -->
    <div id="mailModal" class="modal" role="dialog" aria-hidden="true">
       <div class="modal-content">
          <button class="modal-close">&times;</button>
          <h3 id="mailModalTitle"></h3>
          <p id="mailModalBody"></p>
       </div>
    </div>

    <?php if($sent): ?>
        <p class="empty" style="background:transparent; color:var(--main-color);">Thank you — your request has been sent. We'll contact you soon.</p>
    <?php else: ?>
        <?php if($error): ?>
            <p class="empty" style="background:transparent; color:var(--red);"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

    <form id="contactForm" action="contact_us.php" method="post" style="display:flex; flex-direction:column; gap:1rem;">
        <input type="text" name="name" class="box" placeholder="Your name" required>
        <input type="email" name="email" class="box" placeholder="Your email" required>
        <input type="text" name="phone" class="box" placeholder="Phone number">
        <input type="hidden" name="subject" value="Solar Solution Request">
        <textarea name="message" class="box" placeholder="Project details, location, panels/load/roof info" required></textarea>
        <input type="submit" value="Send Request" class="btn">
    </form>
    <?php endif; ?>

    <p style="margin-top:1rem; color:var(--light-color); font-size:1.4rem;">Tip: For a quicker estimate, use the <a href="calculator.php">Calculator</a> to calculate your daily load.</p>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>