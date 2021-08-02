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
  $getPage = intval($_GET['page']);
  $getLimit = intval($_GET['limit']);

  if($getPage <= 0)
  {
    header('Location: ' . $baseUrlGet . 'katalog_lists.php?page=1&limit=10');

    exit();
  }

  if($getLimit <= 0)
  {
    header('Location: ' . $baseUrlGet . 'katalog_lists.php?page=' . $getPage . '&limit=10');

    exit();
  }

  if($getLimit > 30)
  {
    header('Location: ' . $baseUrlGet . 'katalog_lists.php?page=' . $getPage . '&limit=30');

    exit();
  }
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
<h1 class="mt-4">Katalog Lists</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Katalog Lists</li>
</ol>
<div class="row">
            
</div>
<p style="text-align: center; font-weight: bold;"><?php echo $_SESSION['rumahinapp_admin_tempNotif']; $_SESSION['rumahinapp_admin_tempNotif'] = ''; ?></p>
<p style="text-align: justify;">Bagian ini menampilkan daftar katalog rumah tinggal yang diinputkan oleh pengguna!</p>
<?php
  $startFrom = intval(($getPage - 1) * $getLimit);
  $nextPagePosition = $getPage + 1;
  $nextPageUrl = $baseUrlGet . 'katalog_lists.php?page=' . $getPage . '&limit=' . $getLimit;

  $katalogRumahCount = $conn->prepare("SELECT COUNT(id) as jumlahKatalogRumah FROM katalog_data");
  $katalogRumahCount->execute();

  foreach($katalogRumahCount as $rowKatalogRumahCount)
  {
    $jumlahKatalogRumah = intval($rowKatalogRumahCount['jumlahKatalogRumah']);
  }

  $maxPage = ceil($jumlahKatalogRumah / $getLimit);

  $links = "";
    if ($maxPage >= 1 && $getPage <= $maxPage)
    {
        $links .= "<a style='margin-left: 4px; margin-right: 10px;' href=\"?page=1&limit={$getLimit}\">1</a>";
        $i = max(2, $getPage - 5);

        if ($i > 2)
        {
            $links .= " ... ";
        }

        for (; $i < min($getPage + 6, $maxPage); $i++)
        {
            $links .= "<a style='margin-left: 4px; margin-right: 10px;' href=\"?page={$i}&limit={$getLimit}\">{$i}</a>";
        }

        if ($i != $maxPage)
        {
            $links .= " ... ";
        }

        $links .= "<a style='margin-left: 4px; margin-right: 10px;' href=\"{$url}?page={$maxPage}&limit={$getLimit}\">{$maxPage}</a>";
    }

  echo '<h5><b>Page ' . $getPage . '</b></h5><br>';

  echo $links;

  //echo ceil($jumlahKatalogRumah / 10);

  /*if(($getPage - 1) > 0)
  {
    echo '<a style="margin-right: 10px;" href="' . $baseUrlGet . 'katalog_lists.php?page=' . ($getPage - 1) . '&limit=' . $getLimit . '">' . 'Sebelumnya' . '</a>';
  }

  for($loopPage = 0; $loopPage < ceil($jumlahKatalogRumah / $getLimit); $loopPage++)
  {
    echo '<a style="margin-right: 10px;" href="' . $baseUrlGet . 'katalog_lists.php?page=' . ($loopPage + 1) . '&limit=' . $getLimit . '">' . ($loopPage + 1) . '</a>';

    if($loopPage >= 10)
    {
      echo '<a style="margin-right: 10px;" href="' . $baseUrlGet . 'katalog_lists.php?page=' . ($loopPage + 1) . '&limit=' . $getLimit . '">' . ($loopPage + 1) . '</a>';
    }
  }*/


  
  echo '<br><br>';
?>
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTrackKatalogByUUID"><b>TRACK BY KATALOG UUID</b></button><br><br>
<?php
// katalog status list
// 1 = katalog aktif
// 2 = pending konfirmasi admin
// 3 = terindikasi pelanggaran
// 4 = dihapus
?>
<div style="overflow-x: auto;">
<table class="table">
    <thead class="thead-light">
      <tr>
        <th>No</th>
        <th>Thumbnail</th>
        <th>Nama Katalog</th>
        <th>Published</th>
        <th>Katalog UUID</th>
        <th>Status</th>
        <th>Lihat</th>
      </tr>
    </thead>
    <tbody>
    <?php
        $baseThumbnailImageUrl = "https://api.netspeed.my.id/rumahinapi/";
        $getKatalogData = $conn->prepare("SELECT * FROM katalog_data ORDER BY id DESC LIMIT :getPage, :getLimit");
        $getKatalogData->bindParam(':getPage', $startFrom, PDO::PARAM_INT);
        $getKatalogData->bindParam(':getLimit', $getLimit, PDO::PARAM_INT);
        $getKatalogData->execute();

        $loopNo = 1;

        foreach($getKatalogData as $rowKatalogData)
        {
            $katalogID = intval($rowKatalogData['id']);
            $katalogUUIDGet = $rowKatalogData['katalogUUID'];
            $getStatus = intval($rowKatalogData['status']);

            if($getStatus == 1)
            {
                $statusText = "Aktif / Tayang";
            }

            else if($getStatus == 2)
            {
                $statusText = "Pending ACC Admin";
            }

            else if($getStatus == 3)
            {
                $statusText = "Terindikasi Pelanggaran";
            }

            else if($getStatus == 4)
            {
                $statusText = "Dihapus";
            }

            else
            {
                $statusText = "Unknown";
            }

            $getThumbnail = $conn->prepare("SELECT * FROM katalog_images_data WHERE katalogUUID=:katalogUUID");
            $getThumbnail->bindParam(':katalogUUID', $katalogUUIDGet);
            $getThumbnail->execute();

            foreach($getThumbnail as $rowGetThumbnail)
            {
                $imagesUrl = $rowGetThumbnail['imagesUrl'];
            }

            $fixedThumbnailImageUrl = $baseThumbnailImageUrl . $imagesUrl;

            echo '<tr>
            <td>' . $loopNo . '</td>
            <td><img style="width: 100px; height: 100px;" src="' . $fixedThumbnailImageUrl . '"></td>
            <td>' . $rowKatalogData['katalogName'] . '</td>
            <td>' . date('Y-m-d H:i:s', $rowKatalogData['tayangTimestamp']) . ' WIB' . '</td>
            <td>' . $rowKatalogData['katalogUUID'] . '</td>
            <td>' . $statusText . '<br><br><font style="cursor: pointer;" onclick="openModal(' . $katalogID . ')" data-toggle="modal" data-target="#modalChangeStatusKatalog"><b>( Ubah Status )</b></font></td>
            <td>' . '<font onclick="viewKatalog(' . $katalogID . ')" style="cursor: pointer;"><b>Lihat</b></font>' . '</td>
          </tr>';

          $loopNo++;
        }
    ?>
    </tbody>
  </table>

  <script>
    var setKatalogID = 0;
    function openModal(katalogID)
    {
        setKatalogID = katalogID;
        console.log(setKatalogID);
    }

    function viewKatalog(katalogID)
    {
        var baseHref = '<?php echo $baseUrlGet; ?>';
        //window.location.href = baseHref + 'katalog_details.php?katalogID=' + katalogID;

        window.open(baseHref + 'katalog_details.php?katalogID=' + katalogID, '_blank');
    }
  </script>

  <div class="modal" id="modalChangeStatusKatalog">
    <div class="modal-dialog">
      <div class="modal-content">
      
        <div class="modal-header">
          <h4 class="modal-title">Ganti Status Katalog Rumah</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="form-group">
            <label class="control-label col-sm-offset-2 col-sm-2" for="statusAct">Status</label>
            <div style="width: 100%;">
                <select name="statusAct" id="statusAct" class="form-control">
                <option value="1">(1) - Aktif / Tayang</option>
                <option value="2">(2) - Pending ACC Admin</option>
                <option value="3">(3) - Terindikasi Pelanggaran</option>
                <option value="4">(4) - Dihapus</option>
                </select> 
            </div>
            </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" name="changeStatusBtn" id="changeStatusBtn" class="btn btn-primary">Ganti Status</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
        </div>
      </div>
      </div>
      </div>
        
        <div class="modal" id="modalTrackKatalogByUUID">
      <div class="modal-dialog">
        <div class="modal-content">
        
          <div class="modal-header">
            <h4 class="modal-title">Track Katalog Rumah by UUID</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          
          <div class="modal-body">
              <div class="form-group">
              <label for="katalogUUID">Katalog UUID</label>
              <div style="width: 100%;">
                   <input type="text" class="form-control" name="katalogUUID" id="katalogUUID" placeholder="Katalog Rumah UUID..." value="" required>
              </div>
              </div>

              <p style="text-align: justify; color: red;" name="response" id="response"></p>
          </div>
          
          <div class="modal-footer">
            <button type="button" name="modalTrackKatalogByUUIDBtn" id="modalTrackKatalogByUUIDBtn" class="btn btn-primary">Track</button>
            <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
          </div>
          </div>
          </div>
          </div>

        <script>
            $(document).ready(function(){
                $('#changeStatusBtn').click(function(){
                    var baseHrefUrl = '<?php echo $baseUrlGet; ?>';
                    var getStatusAct = $('#statusAct').val();
                    
                    window.location.href = baseHrefUrl + 'api/ubah_status_katalog.php?katalogID=' + setKatalogID + '&statusAct=' + getStatusAct;
                });

                $('#modalTrackKatalogByUUIDBtn').click(function(){
                  var katalogUUID = $('#katalogUUID').val();
                  $.ajax({
                  type:'POST',
                  crossDomain: true,
                  url: "<?php echo $baseUrlGet; ?>api/track_katalog_by_uuid.php",
                  data:{katalogUUID:katalogUUID},
                  dataType:'JSON',
                  'contentType': 'application/x-www-form-urlencoded',
                  error:function(xhr, ajaxOptions, thrownError){
                  //error
                  $('#response').html('Terjadi kesalahan, silakan coba lagi!');
                  },
                  cache:false,
                  beforeSend:function(request){
                  //sebelum kirim
                  $('#response').html('Sedang memuat, Mohon tunggu...');
                  },
                  success:function(s){

                    if(s['status'] == false)
                    {
                      $('#response').html(s['message']);
                    }

                    if(s['status'] == true)
                    {
                      $('#response').html(s['message'] + '<a href="' + s['data']['goUrl'] + '" target="_blank">' + s['data']['goUrl'] + '</a>'); 
                    }
                  }
                  });
                });
            });
        </script>
        

</div>
</div>
</main>

<?php include 'layouts/footer.php'; ?>