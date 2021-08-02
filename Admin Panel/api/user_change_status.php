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

$getUserID = intval($_GET['userID']);
$getStatusAct = intval($_GET['statusAct']);

// VALIDASI USER ID
$validasiUserID = $conn->prepare("SELECT * FROM users_data WHERE id=:id");
$validasiUserID->bindParam(':id', $getUserID);
$validasiUserID->execute();

if($validasiUserID)
{
    if($validasiUserID->rowCount() <= 0)
    {
        $_SESSION['rumahinapp_admin_tempNotif'] = 'User ID tidak dapat ditemukan atau valid!';
        header('Location: ' . $baseUrlGet . 'user_lists.php');
    }

    if($validasiUserID->rowCount() > 0)
    {
        foreach($validasiUserID as $rowUserID)
        {
            $getUsernameUser = trim($rowUserID['username']);
            $statusNow = intval($rowUserID['status']);
            $namaLengkapUser = htmlspecialchars(trim($rowUserID['namaLengkap']), ENT_QUOTES, 'UTF-8');
            $getEmailOwner = trim($rowUserID['email']);

            if($statusNow == 1)
            {
                $statusNowText = "Aktif";
            }

            if($statusNow == 2)
            {
                $statusNowText = "Dikunci oleh Admin Karena Terindikasi Pelanggaran";
            }

            if($statusNow == 0)
            {
                $statusNowText = "Tidak Aktif";
            }
        }

        if($getStatusAct == $statusNow)
        {
            $_SESSION['rumahinapp_admin_tempNotif'] = 'User "' . $namaLengkapUser . '" sudah berstatus ' . '(' . $statusNow . ') - ' . $statusNowText;
            header('Location: ' . $baseUrlGet . 'user_lists.php');
            exit();
        }

        $statusActText = "";

        if($getStatusAct == 1)
        {
            $statusActText = "Aktif";

            $_SESSION['rumahinapp_admin_tempNotif'] = 'User "' . $namaLengkapUser . '" berhasil diubah ke status ' . '(' . $getStatusAct . ') - ' . $statusActText;

            $updateStatusAkunUser = $conn->prepare("UPDATE users_data SET status=:status WHERE id=:id");
            $updateStatusAkunUser->bindParam(':status', $getStatusAct);
            $updateStatusAkunUser->bindParam(':id', $getUserID);
            $updateStatusAkunUser->execute();
        }

        else if($getStatusAct == 2)
        {
            $statusActText = "Dikunci oleh Admin Karena Terindikasi Pelanggaran";

            $_SESSION['rumahinapp_admin_tempNotif'] = 'User "' . $namaLengkapUser . '" berhasil diubah ke status ' . '(' . $getStatusAct . ') - ' . $statusActText;

            $updateStatusAkunUser = $conn->prepare("UPDATE users_data SET status=:status WHERE id=:id");
            $updateStatusAkunUser->bindParam(':status', $getStatusAct);
            $updateStatusAkunUser->bindParam(':id', $getUserID);
            $updateStatusAkunUser->execute();
        }

        else if($getStatusAct == 0)
        {
            $statusActText = "Tidak Aktif";

            $_SESSION['rumahinapp_admin_tempNotif'] = 'User "' . $namaLengkapUser . '" berhasil diubah ke status ' . '(' . $getStatusAct . ') - ' . $statusActText;

            $updateStatusAkunUser = $conn->prepare("UPDATE users_data SET status=:status WHERE id=:id");
            $updateStatusAkunUser->bindParam(':status', $getStatusAct);
            $updateStatusAkunUser->bindParam(':id', $getUserID);
            $updateStatusAkunUser->execute();
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
            $clientname = $namaLengkapUser;

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
            $mail->SetFrom("noreply@kmsp-store.com", "RumahinApp Account Info");
            $mail->Subject = "Perubahan Status Akun RumahinApp Anda";
            $content = 'Halo, <b>' . $clientname . '</b>.<br><br>' . 'Akun kamu dengan username "<b>' . $getUsernameUser . '</b>": <b>' . $statusActText . '</b>. Apabila kamu menganggap bahwa ini adalah kesalahan kami atau membutuhkan bantuan, silakan hubungi Admin di nomor WhatsApp +6287823867941';

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

        header('Location: ' . $baseUrlGet . 'user_lists.php');
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