<?php
session_start();

require '../db_connection.php';

require '../functions.php';
$bacaFungsi = new functions();
$baseUrlGet = $bacaFungsi->setBaseUrl();

if($_SESSION['rumahinapp_admin_isLogin'] == "ya")
{
    header('Location: ' . $baseUrlGet . 'dashboard.php');
    exit();
}

$requestMethod = $_SERVER['REQUEST_METHOD'];

if($requestMethod != "POST")
{
    $_SESSION['rumahinapp_admin_tempNotif'] = "Metode permintaan harus POST!";
    $_SESSION['rumahinapp_csrf_token'] = "";

    header('Location: ' . $baseUrlGet . 'login.php');
    
    exit();
}

$getCsrfToken = trim($_POST['csrf_token_rumahinapp']);

if($getCsrfToken != $_SESSION['rumahinapp_csrf_token'])
{
    $_SESSION['rumahinapp_admin_tempNotif'] = "INVALID_CSRF_TOKEN";
    $_SESSION['rumahinapp_csrf_token'] = "";
    header('Location: ' . $baseUrlGet . 'login.php');

    exit();
}

$getUsernameOrEmail = trim($_POST['usernameOrEmail']);
$getPassword = trim($_POST['password']);
$hashPassword = hash('sha256', $getPassword . "5TfwtjRVP6y2trmZkTW954zDdWTTAnG5_ed0d6d3b05774cd655409955e467ae6e0cd53d2562f1f0119eeae728aaab8bfb20bbae57913f84a40a965e7ce6a1e9a0686c643952c041b9eac94dcd211e7152");

// VALIDATE
$validateAccount = $conn->prepare("SELECT * FROM users_data WHERE (BINARY username=:username OR email=:email) AND BINARY password=:password AND privilege='admin' LIMIT 1");
$validateAccount->bindParam(':username', $getUsernameOrEmail);
$validateAccount->bindParam(':email', $getUsernameOrEmail);
$validateAccount->bindParam(':password', $hashPassword);
$validateAccount->execute();

if($validateAccount)
{
    if($validateAccount->rowCount() > 0)
    {
        foreach($validateAccount as $rowAccount)
        {
            $fixUsername = trim($rowAccount['username']);
            $_SESSION['rumahinapp_admin_username'] = $fixUsername;
        }

        $_SESSION['rumahinapp_admin_isLogin'] = 'ya';
        $_SESSION['rumahinapp_admin_tempNotif'] = "";
        $_SESSION['rumahinapp_csrf_token'] = "";
        header('Location: ' . $baseUrlGet . 'dashboard.php');
    }

    if($validateAccount->rowCount() <= 0)
    {
        $_SESSION['rumahinapp_admin_tempNotif'] = "Maaf, username/email atau password yang Anda masukkan tidak valid!";
        $_SESSION['rumahinapp_csrf_token'] = "";
        header('Location: ' . $baseUrlGet . 'login.php');
    }
}

else
{
    $_SESSION['rumahinapp_admin_tempNotif'] = "Terjadi kesalahan saat login. Silakan coba lagi!";
    $_SESSION['rumahinapp_csrf_token'] = "";
    header('Location: ' . $baseUrlGet . 'login.php');
}
?>