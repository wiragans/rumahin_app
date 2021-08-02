<?php
// COPYRIGHT 2021 BY WIRA DWI SUSANTO
// SPECIAL THANKS TO BOOTSTRAP, SB ADMIN, OWL CAROUSEL DEV

if($ok == "")
{
    echo "<b>404 NOT FOUND</b>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="RumahinApp Admin Panel" />
        <meta name="author" content="Wira Dwi Susanto" />
        <title>Dashboard - RumahinApp Admin Panel</title>
        <link href="<?php echo $baseUrlGet; ?>css/styles.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <link rel="stylesheet" href="<?php echo $baseUrlGet; ?>dist/assets/owl.carousel.min.css">
        <link rel="stylesheet" href="<?php echo $baseUrlGet; ?>dist/assets/owl.theme.default.min.css">
        <script src="https://code.jquery.com/jquery-3.3.1.min.js" type="text/javascript"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/0.9.0rc1/jspdf.min.js"></script>
    </head>