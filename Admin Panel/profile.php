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
<h1 class="mt-4">Profile</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Profile</li>
</ol>
<div class="row">
            
</div>
<form action="" method="POST">
<div class="form-group">
    <label for="namaLengkap"><b>Nama Lengkap:</b></label>
    <input type="text" class="form-control" placeholder="Nama Lengkap..." name="namaLengkap" id="namaLengkap" maxlength="32" required value="<?php echo $namaLengkapAdmin; ?>">
</div>
<div class="form-group">
    <label for="currentEmail"><b>Email Saat Ini (tidak dapat diganti):</b></label>
    <input type="email" class="form-control" placeholder="Current Email..." name="currentEmail" id="currentEmail" maxlength="64" value="<?php echo $emailAdmin; ?>" disabled style="cursor: no-drop;">
</div>
<div class="form-group">
    <label for="alamat"><b>Alamat:</b></label>
    <input type="text" class="form-control" placeholder="Alamat..." name="alamat" id="alamat" maxlength="255" value="<?php echo $alamatAdmin; ?>">
</div>
<div class="form-group">
    <label for="newPassword"><b>Password Baru (kosongkan jika tidak diubah):</b></label>
    <input type="password" class="form-control" placeholder="Password Baru..." name="newPassword" id="newPassword" maxlength="32" value="">
</div>
<div class="form-group">
    <label for="oldPassword"><b>Password Lama (Wajib diisi untuk konfirmasi):</b></label>
    <input type="password" class="form-control" placeholder="Password Lama..." name="oldPassword" id="oldPassword" maxlength="32" required value="">
</div>
<button type="submit" class="btn btn-primary" name="submitChangeProfile" id="submitChangeProfile">Simpan Perubahan</button>
</form>
<?php
if(isset($_POST['submitChangeProfile']))
{
    $namaLengkapGet = $_POST['namaLengkap'];
    $alamatGet = $_POST['alamat'];
    $newPassword = $_POST['newPassword'];

    $oldPassword = $_POST['oldPassword'];

    $oldPasswordHash = hash('sha256', $oldPassword . "5TfwtjRVP6y2trmZkTW954zDdWTTAnG5_ed0d6d3b05774cd655409955e467ae6e0cd53d2562f1f0119eeae728aaab8bfb20bbae57913f84a40a965e7ce6a1e9a0686c643952c041b9eac94dcd211e7152");

    // VALIDATE PASSWORD
    $validatePassword = $conn->prepare("SELECT * FROM users_data WHERE BINARY username=:username AND BINARY password=:password AND privilege='admin' LIMIT 1");
    $validatePassword->bindParam(':username', $_SESSION['rumahinapp_admin_username']);
    $validatePassword->bindParam(':password', $oldPasswordHash);
    $validatePassword->execute();

    if($validatePassword)
    {
        if($validatePassword->rowCount() <= 0)
        {
            echo '<b>Maaf, password lama yang kamu masukkan tidak valid. Periksa kembali ya!</b>';
        }

        if($validatePassword->rowCount() > 0)
        {
            if(empty(trim($namaLengkapGet)))
            {
                echo '<b>Nama Lengkap Tidak Boleh Kosong!</b><br>';
            }

            if(empty(trim($alamatGet)))
            {
                echo '<b>Alamat Tidak Boleh Kosong!</b><br>';
            }

            // UPDATE DATA
            if(empty(trim($newPassword)))
            {
                $updateDataProfileAdmin = $conn->prepare("UPDATE users_data SET namaLengkap=:namaLengkap, alamat=:alamat WHERE BINARY username=:username AND privilege='admin'");
                $updateDataProfileAdmin->bindParam(':namaLengkap', $namaLengkapGet);
                $updateDataProfileAdmin->bindParam(':alamat', $alamatGet);
                $updateDataProfileAdmin->bindParam(':username', $_SESSION['rumahinapp_admin_username']);
                $updateDataProfileAdmin->execute();
            }

            if(!empty(trim($newPassword)))
            {
                $newPasswordHash = hash('sha256', $newPassword . "5TfwtjRVP6y2trmZkTW954zDdWTTAnG5_ed0d6d3b05774cd655409955e467ae6e0cd53d2562f1f0119eeae728aaab8bfb20bbae57913f84a40a965e7ce6a1e9a0686c643952c041b9eac94dcd211e7152");

                $updateDataProfileAdmin = $conn->prepare("UPDATE users_data SET namaLengkap=:namaLengkap, alamat=:alamat, password=:password WHERE BINARY username=:username AND privilege='admin'");
                $updateDataProfileAdmin->bindParam(':namaLengkap', $namaLengkapGet);
                $updateDataProfileAdmin->bindParam(':alamat', $alamatGet);
                $updateDataProfileAdmin->bindParam(':password', $newPasswordHash);
                $updateDataProfileAdmin->bindParam(':username', $_SESSION['rumahinapp_admin_username']);
                $updateDataProfileAdmin->execute();
            }

            if($updateDataProfileAdmin)
            {
                echo '<b>Update data profile berhasil!</b>';
                echo "<meta http-equiv='refresh' content='2'>";
            }

            else
            {
                echo '<b>Terjadi kesalahan, silakan coba lagi!</b>';
            }
        }
    }

    else
    {
        echo '<b>Terjadi kesalahan, silakan coba lagi!</b>';
    }
}
?>
</div>
</main>

<?php include 'layouts/footer.php'; ?>
