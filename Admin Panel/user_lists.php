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
<h1 class="mt-4">User Lists</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">User Lists</li>
</ol>
<div class="row">
            
</div>

<p style="text-align: center; font-weight: bold;"><?php echo $_SESSION['rumahinapp_admin_tempNotif']; $_SESSION['rumahinapp_admin_tempNotif'] = ''; ?></p>

<div style="overflow-x: auto;">

<table class="table">
    <thead class="thead-light">
      <tr>
        <th>No</th>
        <th>Nama Lengkap</th>
        <th>Username</th>
        <th>Email</th>
        <th>Email Verified</th>
        <th>Alamat</th>
        <th>Waktu Gabung</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
        <?php
            $getUserLists = $conn->prepare("SELECT * FROM users_data WHERE privilege='user' ORDER BY id DESC");
            $getUserLists->execute();

            $loopNo = 1;

            foreach($getUserLists as $rowUserLists)
            {
                echo '<tr>';

                echo '<td>' . $loopNo . '</td>';
                echo '<td>' . $rowUserLists['namaLengkap'] . '</td>';
                echo '<td>' . $rowUserLists['username'] . '</td>';
                echo '<td>' . $rowUserLists['email'] . '</td>';

                if($rowUserLists['emailVerified'] == 1)
                {
                    $emailVerifiedText = "Ya";
                }

                else
                {
                    $emailVerifiedText = "Tidak";
                }

                echo '<td>' . $emailVerifiedText . '</td>';

                if($rowUserLists['status'] == 1)
                {
                    $userStatusText = "Aktif";
                }

                if($rowUserLists['status'] == 2)
                {
                    $userStatusText = "Dikunci Admin";
                }

                if($rowUserLists['status'] == 0)
                {
                    $userStatusText = "Tidak Aktif";
                }

                echo '<td>' . $rowUserLists['alamat'] . '</td>';
                echo '<td>' . date('Y-m-d H:i:s', $rowUserLists['joinTimestamp']) . ' WIB' . '</td>';
                echo '<td>' . $userStatusText . '</td>';
                echo '<td><font style="font-weight: bold; cursor: pointer;" onclick="openModalUbahStatusUser(' . intval($rowUserLists['id']) . ')" data-toggle="modal" data-target="#modalChangeStatusUser">' . 'Ubah Status' . '</font></td>';

                echo '</tr>';

                $loopNo++;
            }
        ?>
    </tbody>
  </table>

  <div class="modal" id="modalChangeStatusUser">
    <div class="modal-dialog">
      <div class="modal-content">
      
        <div class="modal-header">
          <h4 class="modal-title">Ganti Status Akun Useer</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="form-group">
            <label class="control-label col-sm-offset-2 col-sm-2" for="statusAct">Status</label>
            <div style="width: 100%;">
                <select name="statusAct" id="statusAct" class="form-control">
                <option value="1">(1) - Aktif</option>
                <option value="2">(2) - Dikunci Admin Karena Pelanggaran</option>
                <option value="0">(0) - Tidak Aktif</option>
                </select> 
            </div>
            </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" name="changeStatusAkunUserBtn" id="changeStatusAkunUserBtn" class="btn btn-primary">Ganti Status</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
        </div>

</div>

<script>
var userID = null;
function openModalUbahStatusUser(getUserID)
{
    console.log(getUserID);
    userID = getUserID;
}
</script>

</div>
        <script>
            $(document).ready(function(){
                $('#changeStatusAkunUserBtn').click(function(){
                    var baseHrefUrl = '<?php echo $baseUrlGet; ?>';
                    var getStatusAct = $('#statusAct').val();
                    
                    window.location.href = baseHrefUrl + 'api/user_change_status.php?userID=' + userID + '&statusAct=' + getStatusAct;
                });
            });
        </script>
</main>

<?php include 'layouts/footer.php'; ?>