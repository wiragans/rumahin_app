<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';
      
$mail = new PHPMailer();
$mail->IsSMTP();

require '../db_connection.php';

require '../functions.php';
$bacaFungsi = new functions();
$baseUrlGet = $bacaFungsi->setBaseUrl();

if($_SESSION['rumahinapp_admin_isLogin'] != "ya")
{
    header('Location: ' . $baseUrlGet . 'login.php');
    exit();
}

$getKatalogID = intval($_GET['katalogID']);
$getStatusAct = intval($_GET['statusAct']);

// VALIDASI KATALOG ID
$validasiKatalogID = $conn->prepare("SELECT * FROM katalog_data WHERE id=:id");
$validasiKatalogID->bindParam(':id', $getKatalogID);
$validasiKatalogID->execute();

if($validasiKatalogID)
{
    if($validasiKatalogID->rowCount() <= 0)
    {
        $_SESSION['rumahinapp_admin_tempNotif'] = 'Katalog ID tidak dapat ditemukan atau valid!';
        header('Location: ' . $baseUrlGet . 'katalog_lists.php');
    }

    if($validasiKatalogID->rowCount() > 0)
    {
        foreach($validasiKatalogID as $rowKatalogID)
        {
            $getUsernameOwner = trim($rowKatalogID['username']);
            $statusNow = intval($rowKatalogID['status']);
            $namaKatalogRumah = htmlspecialchars(trim($rowKatalogID['katalogName']), ENT_QUOTES, 'UTF-8');

            if($statusNow == 1)
            {
                $statusNowText = "Aktif / Tayang";
            }

            if($statusNow == 2)
            {
                $statusNowText = "Pending ACC Admin";
            }

            if($statusNow == 3)
            {
                $statusNowText = "Terindikasi Pelanggaran";
            }

            if($statusNow == 4)
            {
                $statusNowText = "Dihapus";
            }
        }

        $bacaUsernameOwnerInfo = $conn->prepare("SELECT * FROM users_data WHERE BINARY username=:username");
        $bacaUsernameOwnerInfo->bindParam(':username', $getUsernameOwner);
        $bacaUsernameOwnerInfo->execute();

        foreach($bacaUsernameOwnerInfo as $rowOwnerInfo)
        {
            $getNamaLengkapOwner = htmlspecialchars(trim($rowOwnerInfo['namaLengkap']), ENT_QUOTES, 'UTF-8');
            $getEmailOwner = trim($rowOwnerInfo['email']);
        }

        if($getStatusAct == $statusNow)
        {
            $_SESSION['rumahinapp_admin_tempNotif'] = 'Katalog "' . $namaKatalogRumah . '" sudah berstatus ' . '(' . $statusNow . ') - ' . $statusNowText;
            header('Location: ' . $baseUrlGet . 'katalog_lists.php');
            exit();
        }

        $statusActText = "";

        if($getStatusAct == 1)
        {
            $statusActText = "Aktif / Tayang";

            $_SESSION['rumahinapp_admin_tempNotif'] = 'Katalog "' . $namaKatalogRumah . '" berhasil diubah ke status ' . '(' . $getStatusAct . ') - ' . $statusActText;

            $updateStatusKatalogRumah = $conn->prepare("UPDATE katalog_data SET status=:status WHERE id=:id");
            $updateStatusKatalogRumah->bindParam(':status', $getStatusAct);
            $updateStatusKatalogRumah->bindParam(':id', $getKatalogID);
            $updateStatusKatalogRumah->execute();
        }

        else if($getStatusAct == 2)
        {
            $statusActText = "Pending ACC Admin";

            $_SESSION['rumahinapp_admin_tempNotif'] = 'Katalog "' . $namaKatalogRumah . '" berhasil diubah ke status ' . '(' . $getStatusAct . ') - ' . $statusActText;

            $updateStatusKatalogRumah = $conn->prepare("UPDATE katalog_data SET status=:status WHERE id=:id");
            $updateStatusKatalogRumah->bindParam(':status', $getStatusAct);
            $updateStatusKatalogRumah->bindParam(':id', $getKatalogID);
            $updateStatusKatalogRumah->execute();
        }

        else if($getStatusAct == 3)
        {
            $statusActText = "Terindikasi Pelanggaran";

            $_SESSION['rumahinapp_admin_tempNotif'] = 'Katalog "' . $namaKatalogRumah . '" berhasil diubah ke status ' . '(' . $getStatusAct . ') - ' . $statusActText;

            $updateStatusKatalogRumah = $conn->prepare("UPDATE katalog_data SET status=:status WHERE id=:id");
            $updateStatusKatalogRumah->bindParam(':status', $getStatusAct);
            $updateStatusKatalogRumah->bindParam(':id', $getKatalogID);
            $updateStatusKatalogRumah->execute();
        }

        else if($getStatusAct == 4)
        {
            $statusActText = "Dihapus Oleh Admin";

            $_SESSION['rumahinapp_admin_tempNotif'] = 'Katalog "' . $namaKatalogRumah . '" berhasil diubah ke status ' . '(' . $getStatusAct . ') - ' . $statusActText;

            $updateStatusKatalogRumah = $conn->prepare("UPDATE katalog_data SET status=:status WHERE id=:id");
            $updateStatusKatalogRumah->bindParam(':status', $getStatusAct);
            $updateStatusKatalogRumah->bindParam(':id', $getKatalogID);
            $updateStatusKatalogRumah->execute();
        }

        else
        {
            $statusActText = "";

            $_SESSION['rumahinapp_admin_tempNotif'] = 'Status Act tidak valid!';
        }

        //

        if(!empty(trim($statusActText)))
        {
            $email = $getEmailOwner;
            $clientname = $getNamaLengkapOwner;

            $mail->SMTPDebug  = 0;
            $mail->SMTPAuth   = TRUE;
            $mail->SMTPSecure = "tls";
            $mail->Port       = 587;
            $mail->Host       = "giga.cangkirhost.net";
            $mail->Username   = "noreply@kmsp-store.com";
            $mail->Password   = "TtCuqCr3P";
            $mail->CharSet    = 'UTF-8';

            $mail->IsHTML(true);
            $mail->AddAddress($email, $clientname);
            $mail->SetFrom("noreply@kmsp-store.com", "RumahinApp Info");
            $mail->Subject = "Perubahan Status Katalog Rumah";
            $content = 'Halo, <b>' . $clientname . '</b>.<br><br>' . 'Katalog rumah kamu "<b>' . $namaKatalogRumah . '</b>": <b>' . $statusActText . '</b>. Apabila kamu membutuhkan bantuan, silakan hubungi Admin di nomor WhatsApp +6287823867941';

            $mail->MsgHTML($content);
            
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            if(!$mail->Send())
            {
                //echo "Error while sending Email.";
                //var_dump($mail);
            }

            else
            {
                // sukses
            }
        }

        header('Location: ' . $baseUrlGet . 'katalog_lists.php');
        exit();
    }
}

else
{
    $_SESSION['rumahinapp_admin_tempNotif'] = 'Terjadi kesalahan, silakan coba lagi!';
    header('Location: ' . $baseUrlGet . 'katalog_lists.php');
    exit();
}
?>