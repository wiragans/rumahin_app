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
    $emailAdmin = trim($rowUserDetails['email']);
    $alamatAdmin = trim($rowUserDetails['alamat']);
}
?>

<body>
<div id="layoutSidenav_content">
<main>
<div class="container-fluid px-4">
<h1 class="mt-4">Add Pengumuman</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Add Pengumuman</li>
</ol>
<div class="row">
            
</div>
<form action="" method="POST">
<div class="form-group">
    <label for="pengumumanUUID"><b>Pengumuman UUID:</b></label>
    <input type="text" class="form-control" placeholder="Pengumuman UUID..." name="pengumumanUUID" id="pengumumanUUID" maxlength="40" required value="">
</div>
<div class="form-group">
    <label for="titlePengumuman"><b>Title Pengumuman:</b></label>
    <input type="text" class="form-control" placeholder="Title Pengumuman..." name="titlePengumuman" id="titlePengumuman" maxlength="100" required value="">
</div>
<div class="form-group">
    <label for="contentPengumuman"><b>Content Pengumuman:</b></label>
    <input type="text" class="form-control" placeholder="Content Pengumuman..." name="contentPengumuman" id="contentPengumuman" maxlength="500" required value="">
</div>
<div class="form-group">
    <label for="timestamp"><b>Timestamp:</b></label>
    <input type="text" class="form-control" placeholder="Timestamp..." name="timestamp" id="timestamp" maxlength="40" required value="">
</div>
<button type="submit" class="btn btn-primary" name="submitAddPengumuman" id="submitAddPengumuman">Add Pengumuman</button>
</form>
<?php
if(isset($_POST['submitAddPengumuman']))
{
    $pengumumanUUID = $_POST['pengumumanUUID'];
    $titlePengumuman = $_POST['titlePengumuman'];
    $contentPengumuman = $_POST['contentPengumuman'];
    $timestamp = intval($_POST['timestamp']);
    $isShow = 1;

    $addPengumumannya = $conn->prepare("INSERT INTO pengumuman(pengumumanUUID, titlePengumuman, contentPengumuman, announcedAtTimestamp, isShow) VALUES(:pengumumanUUID, :titlePengumuman, :contentPengumuman, :announcedAtTimestamp, :isShow)");
    $addPengumumannya->bindParam(':pengumumanUUID', $pengumumanUUID);
    $addPengumumannya->bindParam(':titlePengumuman', $titlePengumuman);
    $addPengumumannya->bindParam(':contentPengumuman', $contentPengumuman);
    $addPengumumannya->bindParam(':announcedAtTimestamp', $timestamp);
    $addPengumumannya->bindParam(':isShow', $isShow);
    $addPengumumannya->execute();

    if($addPengumumannya)
    {
        echo '<b>Yayy, pengumuman berhasil ditambahkan dan ditayangkan ke publik</b>';
    }

    else
    {
        echo '<b>Wah, terjadi kesalahan. Silakan coba lagi ya!</b>';
    }
}
?>
</div>
</main>

<?php include 'layouts/footer.php'; ?>