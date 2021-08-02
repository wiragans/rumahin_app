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

$getAct = $_GET['act'];
$idPengumumannya = intval($_GET['idPengumuman']);

if($getAct == "hidePengumuman")
{
    $hidePengumuman = $conn->prepare("UPDATE pengumuman SET isShow=0 WHERE id=:id");
    $hidePengumuman->bindParam(':id', $idPengumumannya);
    $hidePengumuman->execute();

    $_SESSION['rumahinapp_admin_tempNotif'] = "Pengumuman berhasil disembunyikan dari publik!";

    header('Location: ' . $baseUrlGet . 'pengumuman_lists.php');

    exit();
}

if($getAct == "showPengumuman")
{
    $hidePengumuman = $conn->prepare("UPDATE pengumuman SET isShow=1 WHERE id=:id");
    $hidePengumuman->bindParam(':id', $idPengumumannya);
    $hidePengumuman->execute();

    $_SESSION['rumahinapp_admin_tempNotif'] = "Pengumuman berhasil ditampilkan ke publik!";

    header('Location: ' . $baseUrlGet . 'pengumuman_lists.php');

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
<h1 class="mt-4">Pengumuman / Notifikasi Lists</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Pengumuman / Notifikasi Lists</li>
</ol>
<div class="row">
            
</div>

<p style="text-align: center; font-weight: bold;"><?php echo $_SESSION['rumahinapp_admin_tempNotif']; $_SESSION['rumahinapp_admin_tempNotif'] = ''; ?></p>

<div style="overflow-x: auto;">

<table class="table">
    <thead class="thead-light">
      <tr>
        <th>No</th>
        <th>Pengumuman UUID</th>
        <th>Title Pengumuman</th>
        <th>Content Pengumuman</th>
        <th>Announced</th>
        <th>Tampil Publik</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
        <?php
                $loopNo = 1;

                $getPengumumanNotifikasi = $conn->prepare("SELECT * FROM pengumuman ORDER BY id DESC");
                $getPengumumanNotifikasi->execute();

                foreach($getPengumumanNotifikasi as $rowGetPengumumanNotifikasi)
                {
                    $idPengumuman = intval($rowGetPengumumanNotifikasi['id']);
                    $pengumumanUUID = $rowGetPengumumanNotifikasi['pengumumanUUID'];
                    $titlePengumuman = $rowGetPengumumanNotifikasi['titlePengumuman'];
                    $contentPengumuman = $rowGetPengumumanNotifikasi['contentPengumuman'];
                    $announcedAt = intval($rowGetPengumumanNotifikasi['announcedAtTimestamp']);
                    $isShow = intval($rowGetPengumumanNotifikasi['isShow']);

                    if($isShow == 1)
                    {
                        $isShowText = "Ya";
                    }

                    if($isShow != 1)
                    {
                        $isShowText = "Tidak";
                    }

                    $announcedAtReal = date('Y-m-d H:i:s', $announcedAt) . " WIB";

                    echo '<tr>';

                    echo '<td>' . $loopNo . '</td>';
                    echo '<td>' . $pengumumanUUID . '</td>';
                    echo '<td>' . $titlePengumuman . '</td>';
                    echo '<td>' . $contentPengumuman . '</td>';
                    echo '<td>' . $announcedAtReal . '</td>';
                    echo '<td>' . $isShowText . '</td>';

                    if($isShow == 1)
                    {
                        echo '<td><a href="' . $baseUrlGet . 'pengumuman_lists.php?act=hidePengumuman&idPengumuman=' . $idPengumuman . '"><b>Sembunyikan dari Publik</b></a></td>';
                    }
                    
                    if($isShow == 0)
                    {
                        echo '<td><a href="' . $baseUrlGet . 'pengumuman_lists.php?act=showPengumuman&idPengumuman=' . $idPengumuman . '"><b>tampilkan ke Publik</b></a></td>';
                    }

                    echo '</tr>';

                    $loopNo++;
                }
        ?>
    </tbody>
  </table>

</div>

</div>
</main>

<?php include 'layouts/footer.php'; ?>