<?php
include 'components/connect.php'; session_start(); $user_id=$_SESSION['user_id']??''; $message=[]; $dev_link='';
if(isset($_POST['request_reset'])){
  $email=strtolower(trim((string)($_POST['email']??'')));
  $generic='If that email exists, a password reset link has been sent.';
  if(filter_var($email,FILTER_VALIDATE_EMAIL)){
    $stmt=$conn->prepare("SELECT id,name FROM users WHERE email = ? LIMIT 1"); $stmt->execute([$email]); $user=$stmt->fetch(PDO::FETCH_ASSOC);
    if($user){
      $conn->prepare("UPDATE password_reset_tokens SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL")->execute([$user['id']]);
      $raw=bin2hex(random_bytes(32)); $hash=hash('sha256',$raw); $conn->prepare("INSERT INTO password_reset_tokens(user_id,token_hash,expires_at) VALUES(?,?,DATE_ADD(NOW(), INTERVAL 30 MINUTE))")->execute([$user['id'],$hash]);
      $base=rtrim((string)(getenv('APP_BASE_URL')?:('http://'.($_SERVER['HTTP_HOST']??'localhost'))),'/'); $link=$base.'/reset_password.php?token='.$raw;
      $subject='Solar Store password reset'; $body="Hello {$user['name']},\n\nReset your password within 30 minutes:\n{$link}\n\nIf you did not request this, ignore this email."; $from=getenv('MAIL_FROM')?:'no-reply@localhost'; @mail($email,$subject,$body,"From: {$from}\r\nContent-Type: text/plain; charset=UTF-8");
      if((getenv('APP_ENV')?:'development')!=='production')$dev_link=$link;
    }
  }
  $message[]=$generic;
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Forgot password</title><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"><link rel="stylesheet" href="css/style.css"></head><body><?php include 'components/user_header.php'; ?><section class="form-container"><form method="post"><h3>reset password</h3><?php foreach($message as $m): ?><p class="message"><span><?= htmlspecialchars($m,ENT_QUOTES,'UTF-8'); ?></span></p><?php endforeach; ?><input class="box" type="email" name="email" required maxlength="100" placeholder="enter your account email"><input class="btn" type="submit" name="request_reset" value="send reset link"><?php if($dev_link): ?><p class="message">Development reset link: <a href="<?= htmlspecialchars($dev_link,ENT_QUOTES,'UTF-8'); ?>">open reset page</a></p><?php endif; ?><a class="option-btn" href="user_login.php">back to login</a></form></section><?php include 'components/footer.php'; ?><script src="js/script.js"></script></body></html>
