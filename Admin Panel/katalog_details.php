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
<h1 class="mt-4">Katalog Details</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Katalog Details</li>
</ol>
<div class="row">
            
</div>
<?php
// katalog status list
// 1 = katalog aktif
// 2 = pending konfirmasi admin
// 3 = terindikasi pelanggaran
// 4 = dihapus
?>
<?php
    $getKatalogID = intval($_GET['katalogID']);

    // CHECK
    $checkKatalog = $conn->prepare("SELECT * FROM katalog_data WHERE id=:id");
    $checkKatalog->bindParam(':id', $getKatalogID);
    $checkKatalog->execute();

    if($checkKatalog)
    {
        if($checkKatalog->rowCount() <= 0)
        {
            echo '<p style="text-align: justify;"><b>Wah maaf nih, Katalog ID tidak dapat ditemukan!</b></p>';
        }

        if($checkKatalog->rowCount() > 0)
        {
            foreach($checkKatalog as $rowCheckKatalog)
            {
                $katalogName = htmlspecialchars(trim($rowCheckKatalog['katalogName']), ENT_QUOTES, 'UTF-8');
                $katalogUUID = trim($rowCheckKatalog['katalogUUID']);
                $katalogDesc = $rowCheckKatalog['katalogDesc'];
                $alamatRumah = $rowCheckKatalog['alamat'];
                $tahunDibuat = $rowCheckKatalog['tahunDibuat'];
                $kondisiNow = intval($rowCheckKatalog['isSecond']);

                if($kondisiNow == 1) // rumah second / bekas
                {
                    $kondisiNowText = "Rumah Second / Bekas";
                }

                if($kondisiNow == 0) // rumah baru
                {
                    $kondisiNowText = "Rumah Baru";
                }

                $isDisewakan = intval($rowCheckKatalog['isDisewakan']);

                $sistemSewaKeterangan = "";

                if($isDisewakan == 1)
                {
                    $statusTextIsDisewakan = "Disewakan";
                    $sistemSewaKeterangan = $rowCheckKatalog['modeSewa'];
                }

                if($isDisewakan != 1)
                {
                    $statusTextIsDisewakan = "Dijual";
                    $sistemSewaKeterangan = "";
                }

                // DETAIL SPESIFIKASI
                $luasTanah = $rowCheckKatalog['luasTanah'];
                $luasBangunan = $rowCheckKatalog['luasBangunan'];
                $jumlahKamarMandi = intval($rowCheckKatalog['jumlahKamarMandi']);
                $jumlahKamarTidur = intval($rowCheckKatalog['jumlahKamarTidur']);
                $jumlahRuangTamu = intval($rowCheckKatalog['jumlahRuangTamu']);
                $jumlahGarasi = intval($rowCheckKatalog['jumlahGarasi']);
                $jumlahRuangKeluarga = intval($rowCheckKatalog['jumlahRuangKeluarga']);
                $jumlahRuangMakan = intval($rowCheckKatalog['jumlahRuangMakan']);
                $jumlahGudang = intval($rowCheckKatalog['jumlahGudang']);
                $jumlahSerambi = intval($rowCheckKatalog['jumlahSerambi']);
                $jumlahTingkat = intval($rowCheckKatalog['jumlahTingkat']);
                $harga = intval($rowCheckKatalog['harga']);
                $hargaStr = "Rp. " . number_format($harga,2,',','.');
                $usernameOwner = trim($rowCheckKatalog['username']);

                // INFORMASI PENGEMBANG
                $namaPengembang = trim($rowCheckKatalog['developerName']);
                $contactNumber = trim($rowCheckKatalog['contactNumber']);
                $emailDeveloper = $rowCheckKatalog['emailDeveloper'];

                // INFORMASI RUMAH TAMBAHAN
                $kodeTipeRumah = $rowCheckKatalog['kodeTipeRumah'];

                $sertifikatCode = $rowCheckKatalog['sertifikat_code'];
                $sertifikatNameTemp = $rowCheckKatalog['sertifikat'];
            }

            $getKatalogImagesData = $conn->prepare("SELECT * FROM katalog_images_data WHERE katalogUUID=:katalogUUID");
            $getKatalogImagesData->bindParam(':katalogUUID', $katalogUUID);
            $getKatalogImagesData->execute();

            // VIDEO YOUTUBE DATA
            $getKatalogVideoData = $conn->prepare("SELECT * FROM katalog_video_data WHERE katalogUUID=:katalogUUID");
            $getKatalogVideoData->bindParam(':katalogUUID', $katalogUUID);
            $getKatalogVideoData->execute();

            foreach($getKatalogVideoData as $rowVideoData)
            {
                $videoYouTubeUrl = $rowVideoData['videoUrl'];
            }

            // AR 3D DATA
            $getARData = $conn->prepare("SELECT * FROM ar_data WHERE katalogUUID=:katalogUUID");
            $getARData->bindParam(':katalogUUID', $katalogUUID);
            $getARData->execute();

            foreach($getARData as $rowARData)
            {
                $objectFileUrl = $rowARData['objectFileURL'];
                $objectFileDiffuseTextureUrl = $rowARData['objectFileDiffuseTextureURL'];
                $markerUrl = $rowARData['markerUrl'];
            }

            // INFO TAMBAHAN
            $tipeRumahInfo = $conn->prepare("SELECT * FROM rumah_data WHERE BINARY kodeTipeRumah=:kodeTipeRumah");
            $tipeRumahInfo->bindParam(':kodeTipeRumah', $kodeTipeRumah);
            $tipeRumahInfo->execute();

            foreach($tipeRumahInfo as $rowTipeRumahInfo)
            {
                $tipeRumahInString = $rowTipeRumahInfo['tipePropertiRumahInString'];
            }

            // SERTIFIKAT DATA
            $sertifikatData = $conn->prepare("SELECT * FROM sertifikat_lists WHERE BINARY sertifikat_code=:sertifikat_code");
            $sertifikatData->bindParam(':sertifikat_code', $sertifikatCode);
            $sertifikatData->execute();

            foreach($sertifikatData as $rowSertifikatData)
            {
                $sertifikatName = $rowSertifikatData['sertifikat_name'];
                $needManualInput = intval($rowSertifikatData['need_manual_input']);
            }

            if($needManualInput == 1)
            {
                $sertifikatName = $sertifikatNameTemp; // replace dengan manual input apabila jenis sertifikat = LAINNYA
            }

            //echo '<br>';

            echo '<div class="alert alert-success">
            <b>' . $katalogName . '</b>
            </div>';

            echo '<span class="badge badge-warning">Tipe Katalog: ' . $statusTextIsDisewakan . '</span><br><br>';

            if($isDisewakan == 1)
            {
                echo '<span class="badge badge-primary">Keterangan Sistem Sewa: ' . $sistemSewaKeterangan . '</span><br><br>';
            }

            $baseUrlGetKatalog = "https://api.netspeed.my.id/rumahinapi/";

            echo '<div class="owl-carousel">';

            foreach($getKatalogImagesData as $rowImagesData)
            {
                $katalogImageSrcUrl = $baseUrlGetKatalog . $rowImagesData['imagesUrl'];

                echo '<div><img style="width: 90%; height: 200px;" src="' . $katalogImageSrcUrl . '"></div>';
            }

            echo '</div>';

            //

            echo '<hr>';
            echo '<b>Alamat:</b><br><br>';

            echo '<p style="text-align: justify;">' . $alamatRumah . '</p>';

            //

            echo '<hr>';
            echo '<b>Deskripsi:</b><br><br>';

            echo '<p style="text-align: justify;">' . $katalogDesc . '</p>';

            //

            echo '<hr>';

            echo '<b>Detail Spesifikasi:</b><br><br>';

            echo '<div style="overflow-x: auto; margin-bottom: -30px;">';
            echo '<table class="table">
            <thead class="thead-light">
              <tr>
                <th>Luas Tanah</th>
                <th>Luas Bangunan</th>
                <th>Kamar Mandi</th>
                <th>Kamar Tidur</th>
                <th>Ruang Tamu</th>
                <th>Garasi</th>
                <th>Ruang Keluarga</th>
                <th>Ruang Makan</th>
                <th>Gudang</th>
                <th>Serambi</th>
                <th>Tingkat</th>
                <th>Harga</th>
                <th>Username Pemilik</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>' . $luasTanah . '</td>
                <td>' . $luasBangunan . '</td>
                <td>' . $jumlahKamarMandi . '</td>
                <td>' . $jumlahKamarTidur . '</td>
                <td>' . $jumlahRuangTamu . '</td>
                <td>' . $jumlahGarasi . '</td>
                <td>' . $jumlahRuangKeluarga . '</td>
                <td>' . $jumlahRuangMakan . '</td>
                <td>' . $jumlahGudang . '</td>
                <td>' . $jumlahSerambi . '</td>
                <td>' . $jumlahTingkat . '</td>
                <td>' . $hargaStr . '</td>
                <td>' . $usernameOwner . '</td>
              </tr>
            </tbody>
          </table>';
            
            echo '</div>';

            //

            echo '<hr>';

            $miscSpecData = $conn->prepare("SELECT * FROM misc_katalog_spec WHERE katalogUUID=:katalogUUID");
            $miscSpecData->bindParam(':katalogUUID', $katalogUUID);
            $miscSpecData->execute();

            foreach($miscSpecData as $rowMiscSpecData)
            {
                $conditionMeasurement = $rowMiscSpecData['conditionMeasurement'];
                $perlengkapanPerabotan = $rowMiscSpecData['perlengkapanPerabotan'];
                $dayaListrik = $rowMiscSpecData['dayaListrik'];
            }

            echo '<b>Informasi Tambahan:</b><br><br>';

            echo '<div style="overflow-x: auto; margin-bottom: -30px;">';
            echo '<table class="table">
            <thead class="thead-light">
              <tr>
                <th>Tipe Rumah</th>
                <th>Pemastian Kondisi</th>
                <th>Perlengkapan/Perabotan</th>
                <th>Daya Listrik</th>
                <th>Tahun Pembuatan</th>
                <th>Kondisi</th>
                <th>Sertifikat</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>' . $tipeRumahInString . '</td>
                <td>' . $conditionMeasurement . '</td>
                <td>' . $perlengkapanPerabotan . '</td>
                <td>' . $dayaListrik . ' watt' . '</td>
                <td>' . $tahunDibuat . '</td>
                <td>' . $kondisiNowText . '</td>
                <td>' . $sertifikatName . ' (' . $sertifikatCode . ')' . '</td>
              </tr>
            </tbody>
          </table>';

            //

            echo '<font>- Link Video YouTube: <a href="' . $videoYouTubeUrl . '" target="_blank">' . $videoYouTubeUrl . '</a></font><br>';

            echo '<font>- Link File FBX 3D: <a href="' . $objectFileUrl . '" target="_blank">' . $objectFileUrl . '</a></font><br>';

            echo '<font>- Link Diffuse Texture FBX 3D: <a href="' . $objectFileDiffuseTextureUrl . '" target="_blank">' . $objectFileDiffuseTextureUrl . '</a></font><br>';

            echo '<font>- Link Gambar Marker AR: <a href="' . $markerUrl . '" target="_blank">' . $markerUrl . '</a></font><br>';

            echo '<hr>';

            echo '<b>Informasi Pengembang (Developer):</b><br><br>';

            if(empty(trim($emailDeveloper)))
            {
                $emailDeveloper = "-";
            }

            echo '<font>- Nama Pengembang: ' . $namaPengembang . '</font><br>';
            echo '<font>- Contact Number (WhatsApp): ' . $contactNumber . '</font><br>';
            echo '<font>- Email Pengembang: ' . $emailDeveloper . '</font>';
        }
    }

    else
    {
        //echo "Wah terjadi kesalahan saat menampilan details katalog rumah, silakan coba reload halaman ini ya!";
        echo '<p style="text-align: justify;"><b>Wah terjadi kesalahan saat menampilan details katalog rumah, silakan coba reload halaman ini ya!</b></p>';
    }
?>

<div style="margin-bottom: 50px;"></div>

<script>
$(document).ready(function(){
  $(".owl-carousel").owlCarousel({
      margin: 10,
      responsiveClass: true
  });
});
</script>

</div>
</main>

<?php include 'layouts/footer.php'; ?>