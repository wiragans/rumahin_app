<?php
if($ok == "")
{
    echo "<b>404 NOT FOUND</b>";
    exit();
}
?>
<footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">&copy; RumahinApp <?php echo date('Y'); ?>. All rights reserved - Wira Dwi Susanto</div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="<?php echo $baseUrlGet; ?>js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="<?php echo $baseUrlGet; ?>dist/owl.carousel.min.js"></script>
    </body>
</html>