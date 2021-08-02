<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

require 'functions.php';
$bacaFungsi = new functions();
$baseUrlGet = $bacaFungsi->setBaseUrl();

if(empty(trim($_SESSION['rumahinapp_csrf_token'])))
{
    $randomKey = bin2hex(openssl_random_pseudo_bytes(64));
    $csrf_token = hash('sha256', $randomKey);

    $_SESSION['rumahinapp_csrf_token'] = $csrf_token;
}

if($_SESSION['rumahinapp_admin_isLogin'] == "ya")
{
    header('Location: ' . $baseUrlGet . 'dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="format-detection" content="telephone=no" />
<meta name="robots" content="index, follow">
<meta name="author" content="Wira Dwi Susanto">
<title>RumahinApp Admin Panel</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</head>
<body>
<style>
body{background-color: #E1E1E1;}

@media only screen and (min-width: 200px) {
.atur1{width: 90%;}
.atur2{font-size: 12px;}
.atur3{font-size: 12px;}
.atur4{font-size: 12px;}
}

@media only screen and (min-width: 600px) {
.atur1{width: 80%;}
.atur2{font-size: 14px;}
.atur3{font-size: 14px;}
.atur4{font-size: 14px;}
}

@media only screen and (min-width: 600px) {
.atur1{width: 80%;}
.atur2{font-size: 15px;}
.atur3{font-size: 15px;}
.atur4{font-size: 15px;}
}

@media only screen and (min-width: 1000px) {
.atur1{width: 60%;}
.atur2{font-size: 16px;}
.atur3{font-size: 16px;}
.atur4{font-size: 16px;}
}

@media only screen and (min-width: 1300px) {
.atur1{width: 40%;}
.atur2{font-size: 16px;}
.atur3{font-size: 16px;}
.atur4{font-size: 16px;}
}
</style>
<div class="container atur1" style="margin-top: 110px; margin-bottom: 90px; background-color: white; padding: 20px; border-radius: 4px;">
<center><h4><b>RumahinApp Admin Panel</b></h4></center>
<p style="text-align: center; color: red;"><?php echo $_SESSION['rumahinapp_admin_tempNotif']; $_SESSION['rumahinapp_admin_tempNotif'] = ''; ?></p>
<form action="<?php echo $baseUrlGet . 'api/login.php'; ?>" method="POST">
<div class="form-group">
<label for="usernameOrEmail"><i style="padding-left: 4px;" class="fa fa-user" aria-hidden="true"></i> Username/Email:</label>
<input type="text" class="form-control" name="usernameOrEmail" id="usernameOrEmail" value="" placeholder="Username/Email Anda..." maxlength="64" required>
</div>
<div class="form-group">
<label for="password"><i style="padding-left: 4px;" class="fa fa-lock" aria-hidden="true"></i> Password:</label>
<input type="password" class="form-control" name="password" id="password" value="" placeholder="Password Anda..." maxlength="64" required>
</div>
<input type="hidden" name="csrf_token_rumahinapp" id="csrf_token_rumahinapp" value="<?php echo $_SESSION['rumahinapp_csrf_token']; ?>">
<center><p class="text-danger" name="response" id="response"></p></center>
<button type="submit" name="submitLogin" id="submitLogin" class="btn btn-primary"><i class="fa fa-sign-in" aria-hidden="true"></i> Login Admin</button>
</form>
</div>
<script type="text/javascript">
$(document).ready(function(){

});
</script>
</body>
</html>