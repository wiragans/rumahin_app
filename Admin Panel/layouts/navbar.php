<?php
if($ok == "")
{
    echo "<b>404 NOT FOUND</b>";
    exit();
}
?>
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <a style="margin-left: 10px;" class="navbar-brand" href="<?php echo $baseUrlGet . 'dashboard.php'; ?>"><b>RumahinApp Admin</b></a>
            <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
            <form class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
                
            </form>
            <!-- Navbar-->
            <ul class="navbar-nav ml-auto ml-md-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="userDropdown" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="<?php echo $baseUrlGet . 'profile.php'; ?>">Lihat Profile</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?php echo $baseUrlGet . 'logout.php'; ?>">Logout / Keluar</a>
                </div>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                    
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading">Navigation</div>
                            <a class="nav-link" href="<?php echo $baseUrlGet . 'dashboard.php'; ?>">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Home
                            </a>
                            <a class="nav-link" href="<?php echo $baseUrlGet . 'katalog_lists.php?page=1&limit=10'; ?>">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Katalog Lists
                            </a>
                            <a class="nav-link" href="<?php echo $baseUrlGet . 'user_lists.php'; ?>">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                User Lists
                            </a>
                            <a class="nav-link" href="<?php echo $baseUrlGet . 'pengumuman_lists.php'; ?>">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Pengumuman Lists
                            </a>
                            <a class="nav-link" href="<?php echo $baseUrlGet . 'add_pengumuman.php'; ?>">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Add Pengumuman
                            </a>
                        
                    </div>
                </nav>
            </div>