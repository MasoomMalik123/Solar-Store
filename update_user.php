<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

if($user_id === ''){
   header('location:user_login.php');
   exit;
}

$select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

if(!is_array($fetch_profile)){
   header('location:user_login.php');
   exit;
}

if(isset($_POST['submit'])){
   $name = trim($_POST['name'] ?? '');
   $email = trim($_POST['email'] ?? '');
   $old_password = $_POST['old_pass'] ?? '';
   $new_password = $_POST['new_pass'] ?? '';
   $confirm_password = $_POST['cpass'] ?? '';
   $errors = [];
   $new_image = null;

   if($name === '' || strlen($name) > 20){
      $errors[] = 'Please enter a name up to 20 characters.';
   }
   if(!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 50){
      $errors[] = 'Please enter a valid email address.';
   }

   $check_email = $conn->prepare("SELECT id FROM `users` WHERE email = ? AND id != ?");
   $check_email->execute([$email, $user_id]);
   if($check_email->fetch()){
      $errors[] = 'That email address is already in use.';
   }

   $password_requested = $old_password !== '' || $new_password !== '' || $confirm_password !== '';
   if($password_requested){
      if($old_password === '' || !hash_equals($fetch_profile['password'], sha1($old_password))){
         $errors[] = 'Your current password is incorrect.';
      }elseif($new_password === '' || $new_password !== $confirm_password){
         $errors[] = 'Enter and confirm the same new password.';
      }
   }

   if(isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE){
      if($_FILES['image']['error'] !== UPLOAD_ERR_OK){
         $errors[] = 'The profile photo could not be uploaded.';
      }elseif($_FILES['image']['size'] > 2 * 1024 * 1024){
         $errors[] = 'Choose a profile photo smaller than 2 MB.';
      }else{
         $file_info = new finfo(FILEINFO_MIME_TYPE);
         $mime_type = $file_info->file($_FILES['image']['tmp_name']);
         $allowed_images = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
         ];
         if(!isset($allowed_images[$mime_type])){
            $errors[] = 'Choose a JPG, PNG, or WebP profile photo.';
         }else{
            $new_image = bin2hex(random_bytes(16)) . '.' . $allowed_images[$mime_type];
         }
      }
   }

   if(empty($errors)){
      if($new_image !== null && !move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/uploaded_img/' . $new_image)){
         $errors[] = 'The profile photo could not be saved.';
      }else{
         $updates = ['name = ?', 'email = ?'];
         $values = [$name, $email];
         if($password_requested){
            $updates[] = 'password = ?';
            $values[] = sha1($new_password);
         }
         if($new_image !== null){
            $updates[] = 'image = ?';
            $values[] = $new_image;
         }
         $values[] = $user_id;
         $update_profile = $conn->prepare('UPDATE `users` SET ' . implode(', ', $updates) . ' WHERE id = ?');
         $update_profile->execute($values);

         if($new_image !== null && !empty($fetch_profile['image'])){
            $old_image_path = __DIR__ . '/uploaded_img/' . basename($fetch_profile['image']);
            if(is_file($old_image_path)) unlink($old_image_path);
         }
         $message[] = 'Your profile has been updated.';
         $select_profile->execute([$user_id]);
         $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
      }
   }

   foreach($errors as $error){
      $message[] = $error;
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Manage profile</title>
   
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="form-container">

   <form action="" method="post" enctype="multipart/form-data">
      <h3>Manage profile</h3>
      <?php if(!empty($fetch_profile['image']) && is_file(__DIR__ . '/uploaded_img/' . basename($fetch_profile['image']))): ?>
         <img src="uploaded_img/<?= htmlspecialchars($fetch_profile['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Current profile photo" class="profile-photo-preview">
      <?php else: ?>
         <img src="images/icon-3.png" alt="Default profile photo" class="profile-photo-preview">
      <?php endif; ?>
      <label for="profile-image" class="profile-image-label">Profile photo</label>
      <input type="file" id="profile-image" name="image" accept="image/jpeg,image/png,image/webp" class="box">
      <input type="text" name="name" required placeholder="Your name" maxlength="20" class="box" value="<?= htmlspecialchars((string)($fetch_profile['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
      <input type="email" name="email" required placeholder="Your email" maxlength="50" class="box" oninput="this.value = this.value.replace(/\s/g, '')" value="<?= htmlspecialchars((string)($fetch_profile['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
      <h4>Change password</h4>
      <input type="password" name="old_pass" placeholder="Current password" maxlength="100" class="box" autocomplete="current-password">
      <input type="password" name="new_pass" placeholder="New password" maxlength="100" class="box" autocomplete="new-password">
      <input type="password" name="cpass" placeholder="Confirm new password" maxlength="100" class="box" autocomplete="new-password">
      <input type="submit" value="Save changes" class="btn" name="submit">
   </form>

</section>













<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>