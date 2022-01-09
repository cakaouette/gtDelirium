<?php
require("NavBar.php");
require("SideBar.php");
require("Footer.php");
require("ContentHeader.php");

function printMsg($type, $msg) {
    echo '<div class="alert alert-'.$type.' alert-dismissible">';
    echo   '<button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="fas fa-times"></i></button>';
    echo   $msg;
    echo '</div>';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=0.55">
    <title><?= $v_title  ?? "" ?></title>
    <!-- <link href="style.css" rel="stylesheet" />-->
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="dist/AdminLTE-3.1.0/plugins/fontawesome-free/css/all.min.css">
    <!-- IonIcons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/AdminLTE-3.1.0/dist/css/adminlte.min.css">
    <?= $specStyle  ?? "" ?>
</head>

<body class="hold-transition sidebar-mini <?php if (($_SESSION["debug"] ?? NULL) != 1) {echo "layout-navbar-fixed layout-fixed";}?>">
<div class="wrapper">
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
<?= $navigation ?>
</nav>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <?= $sidebar ?>
</aside>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<?php if (isset($v_msgs)) { 
    foreach ($v_msgs["success"] as $msg) {
        printMsg("success", $msg);
    }
    foreach ($v_msgs["danger"] as $msg) {
        printMsg("danger", $msg);
    }
    foreach ($v_msgs["warning"] as $msg) {
        printMsg("warning", $msg);
    }
    foreach ($v_msgs["primary"] as $msg) {
        printMsg("primary", $msg);
    }
    foreach ($v_msgs["secondary"] as $msg) {
        printMsg("secondary", $msg);
    }
    foreach ($v_msgs["info"] as $msg) {
        printMsg("info", $msg);
    }
}
?>
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <?= $content_header ?? "" ?>
    </div><!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <?= $content ?? "" ?>
<!--        <a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top">
          <i class="fas fa-chevron-up"></i>
        </a>-->
      </div>
      <?= $modal ?? "" ?>
    </div><!-- /.content -->

</div>
<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
<!-- Main Footer -->
<footer class="main-footer">
    <?= $footer ?>
</footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="dist/AdminLTE-3.1.0/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="dist/AdminLTE-3.1.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="dist/AdminLTE-3.1.0/dist/js/adminlte.js"></script>
<?= $specScript  ?? "" ?>

</body>
</html>