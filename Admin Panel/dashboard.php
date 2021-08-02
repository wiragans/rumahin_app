<?php
// COPYRIGHT BY WIRA DWI SUSANTO 2021
// SPECIAL THANKS TO SB ADMIN DEV

session_start();
date_default_timezone_set('Asia/Jakarta');

require 'db_connection.php';

require 'functions.php';
$bacaFungsi = new functions();
$baseUrlGet = $bacaFungsi->setBaseUrl();

if($_SESSION['rumahinapp_admin_isLogin'] != "ya")
{
    header('Location: ' . $baseUrlGet . 'login.php');
    exit();
}

$ok = "ok";
?>
<?php
include 'layouts/header.php';
include 'layouts/navbar.php';
?>

<?php
$getUserDetails = $conn->prepare("SELECT * FROM users_data WHERE BINARY username=:username AND privilege='admin'");
$getUserDetails->bindParam(':username', $_SESSION['rumahinapp_admin_username']);
$getUserDetails->execute();

foreach($getUserDetails as $rowUserDetails)
{
    $namaLengkapAdmin = htmlspecialchars(trim($rowUserDetails['namaLengkap']), ENT_QUOTES, 'UTF-8');
}
?>

<body>
<div id="layoutSidenav_content">
<main>
<div class="container-fluid px-4">
<h1 class="mt-4">Dashboard</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Dashboard</li>
</ol>
<div class="row">
            
</div>
<h6 style="margin-left: 2px; margin-right: 10px; word-break: break-all; text-align: center; font-weight: bold;">
        Selamat Datang, <?php echo $namaLengkapAdmin; ?>
</h6>   
</div>
</main>

<?php include 'layouts/footer.php'; ?>
