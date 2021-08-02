<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require APPPATH . '/libraries/SMTP/Exception.php';
require APPPATH . '/libraries/SMTP/PHPMailer.php';
require APPPATH . '/libraries/SMTP/SMTP.php'; 
date_default_timezone_set('Asia/Jakarta');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Email Confirmation Page</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
  <link rel="icon" type="image/png" href="https://www.kmsp-store.com/kmsp-favicon.ico" />
  <style type="text/css" rel="stylesheet" media="all">
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    /* Firefox */
    input[type=number] {
      -moz-appearance: textfield;
    }

    /* Base ------------------------------ */
    *:not(br):not(tr):not(html) {
      font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
      -webkit-box-sizing: border-box;
      box-sizing: border-box;
    }

    body {
      width: 100% !important;
      height: 100%;
      margin: 0;
      line-height: 1.4;
      background-color: #F5F7F9;
      color: #839197;
      -webkit-text-size-adjust: none;
    }

    a {
      color: #414EF9;
    }

    /* Layout ------------------------------ */
    .email-wrapper {
      width: 100%;
      margin: 0;
      padding: 0;
      background-color: #F5F7F9;
    }

    .email-content {
      width: 100%;
      margin: 0;
      padding: 0;
    }

    /* Masthead ----------------------- */
    .email-masthead {
      padding: 25px 0;
      text-align: center;
    }

    .email-masthead_logo {
      max-width: 400px;
      border: 0;
    }

    .email-masthead_name {
      font-size: 16px;
      font-weight: bold;
      color: #839197;
      text-decoration: none;
      text-shadow: 0 1px 0 white;
    }

    /* Body ------------------------------ */
    .email-body {
      width: 100%;
      margin: 0;
      padding: 0;
      border-top: 1px solid #E7EAEC;
      border-bottom: 1px solid #E7EAEC;
      background-color: #FFFFFF;
    }

    .email-body_inner {
      width: 570px;
      margin: 0 auto;
      padding: 0;
    }

    .email-footer {
      width: 570px;
      margin: 0 auto;
      padding: 0;
      text-align: center;
    }

    .email-footer p {
      color: #839197;
    }

    .body-action {
      width: 100%;
      margin: 30px auto;
      padding: 0;
      text-align: center;
    }

    .body-sub {
      margin-top: 25px;
      padding-top: 25px;
      border-top: 1px solid #E7EAEC;
    }

    .content-cell {
      padding: 35px;
    }

    .align-right {
      text-align: right;
    }

    /* Type ------------------------------ */
    h1 {
      margin-top: 0;
      color: #292E31;
      font-size: 19px;
      font-weight: bold;
      text-align: left;
    }

    h2 {
      margin-top: 0;
      color: #292E31;
      font-size: 16px;
      font-weight: bold;
      text-align: left;
    }

    h3 {
      margin-top: 0;
      color: #292E31;
      font-size: 14px;
      font-weight: bold;
      text-align: left;
    }

    p {
      margin-top: 0;
      color: #839197;
      font-size: 16px;
      line-height: 1.5em;
      text-align: left;
    }

    p.sub {
      font-size: 12px;
    }

    p.center {
      text-align: center;
    }

    /* Buttons ------------------------------ */
    #close_window {
      margin-bottom: 20px;
      text-decoration: none;
    }

    a>#close_window:hover {
      margin-bottom: 20px;
      text-decoration: none;
      color: #839197;
    }

    #error>p {
      color: rgb(212, 19, 65) !important;
      font-weight: bold !important;
    }

    #json>p {
      color: rgb(212, 19, 65) !important;
      font-weight: bold !important;
    }

    .button {
      display: inline-block;
      width: 200px;
      background-color: #414EF9;
      border-radius: 3px;
      color: #ffffff;
      font-size: 15px;
      line-height: 45px;
      text-align: center;
      text-decoration: none;
      -webkit-text-size-adjust: none;
      mso-hide: all;
    }

    .button--green {
      background-color: #28DB67;
    }

    .button--red {
      background-color: #FF3665;
    }

    .button--blue {
      font-weight: bold;
      background-color: #839197;
      text-shadow: none;
      border-style: outset;
      border: none;
    }

    .button--success {
      font-weight: bold;
      border-radius: 10px;
      background-color: #410EF9;
    }

    .button--success:hover {
      color: #ffffff;
    }

    input {
      color: #410EF9;
      font-weight: 600;
      text-align: center;
    }

    /*Media Queries ------------------------------ */
    @media only screen and (max-width: 600px) {

      .email-body_inner,
      .email-footer {
        width: 100% !important;
      }
    }

    @media only screen and (max-width: 500px) {
      .button {
        width: 100% !important;
      }
    }
  </style>
</head>
<body>
  <table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" style="margin-top: 80px; margin-bottom: 50px;>
    <tr>
      <td align="center">
        <table class="email-content" width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td class="email-body" width="100%">
              <table class="email-body_inner" align="center" width="570" cellpadding="0" cellspacing="0">
                <!-- Body content -->
                <tr>
                  <td class="content-cell">
                    <h1>Konfirmasi Email Anda</h1>
                      <p>Konfirmasi ulang Password Rumahin Anda untuk melanjutkan! (Link ini akan expired dalam 7 hari kedepan, terhitung sejak registrasi akun RumahinApp)</p>
                        <form action="" method="POST">
                        <input type="hidden" id="tokenVerifikasiEmail" name="tokenVerifikasiEmail" value="<?php echo $this->input->get('token', TRUE); ?>">
                        <table class="body-action" align="center" width="100%" cellpadding="0" cellspacing="0">
                          <tr align="left">
                            <div class="myPanel">
                              <td>Password</td>
                              <td>:</td>
                              <td><input id="password" type="password" maxlength="32" name="password" required placeholder="Password Rumahin..." value=""></td>
                            </div>
                          </tr>
                          <tr align="left">
                            <td colspan="3">
                              <div>
                                <br>
                                <button id="buttonSubmitConfirmationEmail" style="background-color: blue;" class="button button--blue" name="buttonSubmitConfirmationEmail" type="submit">Konfirmasi Email</button>
                              </div>
                            </td>
                          </tr>
                        </table>
                        </form>
                        <?php
                          if(isset($_POST['buttonSubmitConfirmationEmail']) && (!empty(isset($_POST['password']))))
                          {
                             $passwordHash = hash('sha256', $_POST['password'] . $this->config->item('saltHash1'));
                             $checkDataInDB = $this->db->query("SELECT tokenVerifikasiEmail, tokenVerifikasiEmailExpired, password FROM users_data WHERE BINARY tokenVerifikasiEmail = ? AND BINARY password = ?", array($_POST['tokenVerifikasiEmail'], $passwordHash));
                             $resultCheckDataInDB = $checkDataInDB->result_array();

                             if(count($resultCheckDataInDB) <= 0)
                             {
                               echo "<b>Password Anda salah. Silakan cek kembali!</b>";
                             }

                             if(count($resultCheckDataInDB) > 0)
                             {
                                foreach($resultCheckDataInDB as $rowCheckData)
                                {
                                  $tokenVerifikasiEmailExpired = intval($rowCheckData['tokenVerifikasiEmailExpired']);
                                }

                                $waktuNow = time();
                                $selisihWaktuNow = $tokenVerifikasiEmailExpired - $waktuNow;

                                if($selisihWaktuNow <= 0)
                                {
                                  echo "<b>Link tidak valid atau sudah tidak berlaku lagi!</b>";
                                }

                                if($selisihWaktuNow > 0)
                                {
                                  // GET USER DATA UNTUK DIINFO KE EMAIL
                                  $getDataUser = $this->db->query("SELECT namaLengkap, username, email, tokenVerifikasiEmail FROM users_data WHERE BINARY tokenVerifikasiEmail = ?", array($_POST['tokenVerifikasiEmail']));
                                  $resultGetDataUser = $getDataUser->result_array();

                                  foreach($resultGetDataUser as $rowGetDataUser)
                                  {
                                    $namaLengkapnya = htmlentities(trim($rowGetDataUser['namaLengkap']), ENT_QUOTES, 'UTF-8');
                                    $usernameUsernya = htmlentities(trim($rowGetDataUser['username']), ENT_QUOTES, 'UTF-8');
                                    $emailUsernya = htmlentities(trim($rowGetDataUser['email']), ENT_QUOTES, 'UTF-8');
                                  }

                                  // UPDATE DATA USERSNYA (EMAIL VEFIFIED)
                                  $setEmailVerified = $this->db->query("UPDATE users_data SET emailVerified = 1 WHERE BINARY tokenVerifikasiEmail = ? AND email = ? AND emailVerified <> 1", array($_POST['tokenVerifikasiEmail'], $emailUsernya));

                                  $mail = new PHPMailer();
                                  $mail->IsSMTP();
                              
                                  $mail->SMTPDebug  = 0;
                                  $mail->SMTPAuth   = TRUE;
                                  $mail->SMTPSecure = "tls";
                                  $mail->Port       = 587;
                                  $mail->Host       = "giga.cangkirhost.net";
                                  $mail->Username   = "noreply@kmsp-store.com";
                                  $mail->Password   = "TtCuqCr3P";
                                  $mail->CharSet = 'UTF-8';
                              
                                  $mail->IsHTML(true);
                                  $mail->AddAddress($emailUsernya, $usernameUsernya);
                                  $mail->SetFrom("noreply@kmsp-store.com", "RumahinApp");
                                  $mail->Subject = "Verifikasi Email Akun RumahinApp Berhasil";
                                  $content = 'Halo, <b>' . $namaLengkapnya . '</b>.<br><br>Konfirmasi email sukses, terima kasih telah bergabung di RumahinApp. Akun Anda sekarang sudah aktif :)<br><br>( Pesan ini adalah pesan otomatis yang di-generate oleh sistem. Jangan membalas pesan ini! )';
                              
                                  $mail->MsgHTML($content);
                                  
                                  $mail->SMTPOptions = array(
                                    'ssl' => array(
                                      'verify_peer' => false,
                                      'verify_peer_name' => false,
                                      'allow_self_signed' => true
                                    )
                                  );
                              
                                  //var_dump($mail);
                                  
                                  if(!$mail->Send())
                                  {
                                    //return false;
                                  }
                              
                                  else
                                  {
                                    //return true;
                                  }

                                echo "<b>Konfirmasi email sukses, terima kasih telah bergabung di RumahinApp. Akun Anda sekarang sudah aktif :)</b>";
                               }
                            }
                          }
                        ?>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td>
              <table class="email-footer" align="center" width="570" cellpadding="0" cellspacing="0">
                <tr>
                  <td class="content-cell">
                    <p class="sub center">
                      RUMAHINAPP 2021
                      <br>Developed By: Wira Dwi Susanto
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <script src="https://code.jquery.com/jquery-3.4.1.js"></script>
</body>
</html>