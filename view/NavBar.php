<?php ob_start(); ?>
<!-- Left navbar links -->
<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
        <a href="index.php" class="nav-link">Home</a>
    </li>
</ul>

<!-- Right navbar links -->
<ul class="navbar-nav ml-auto">
    <?php if ($_SESSION["grade"] <= $_SESSION["Gestion"]) { ?>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="?page=debug" class="nav-link">debug</a>
        </li>
    <?php } ?>
    <li class="nav-item d-none d-sm-inline-block">
        <a href="?page=profile" class="nav-link">
            <i class="nav-icon fas fa-user"></i>
            Profil
        </a>
    </li>
    <?php if ($_SESSION["grade"] < $_SESSION["Visiteur"]) {?>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="?page=disconnect" class="nav-link">
                Déconnexion
                <i class="nav-icon fas fa-sign-out-alt"></i>
            </a>
        </li>
    <?php } else { ?>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="?page=connect" class="nav-link">
                <i class="nav-icon fas fa-sign-in-alt"></i>
                Connexion
            </a>
        </li>
    <?php } ?>
</ul>

<?php $navigation = ob_get_clean(); ?>

