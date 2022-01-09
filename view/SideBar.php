<?php
include_once('tools/utils.php');

function printClass($attr, $p, $sp = NULL) {
    return ((getVar($_GET, "page") == $p and (is_null($sp) or (getVar($_GET,"subpage") == $sp))) ? $attr : "");
}
?>

<?php ob_start(); ?>
<a href="index.php" class="brand-link">
    <img src="dist/image/delirium-logo1.jpg" alt="delirium Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light">Alliance de la Delirium</span>
</a>
<?php 
      
?>
<div class="sidebar">
    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <!--Alliance-->
            <li class="nav-item">
                <a href="?page=alliance" class="nav-link">
                    <i class="nav-icon fas fa-shield-alt text-purple"></i>
                    <p>L'alliance</p>
                </a>
            </li>
        <?php if ($_SESSION["grade"] <= $_SESSION["Officier"]) {?>
            <!--Administration-->
            <li class="nav-item <?=printClass("menu-open", "admin")?>">
                <a href="#" class="nav-link <?=printClass("active", "admin")?>">
                    <i class="nav-icon fas fa-tools text-fuchsia"></i>
                    <p>
                        Administration
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="?page=admin&subpage=dashboard" class="nav-link <?=printClass("active", "admin", "dashboard")?>">
                            <i class="nav-icon fas fa-tachometer-alt text-fuchsia"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                </ul>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="?page=admin&subpage=members" class="nav-link <?=printClass("active", "admin", "members")?>">
                            <i class="nav-icon fas fa-user-lock text-fuchsia"></i>
                            <p>Membres</p>
                        </a>
                    </li>
                </ul>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="?page=admin&subpage=heros" class="nav-link <?=printClass("active", "admin", "heros")?>">
                            <i class="nav-icon fas fa-restroom text-fuchsia"></i>
                            <p>Héros & Armes</p>
                        </a>
                    </li>
                </ul>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="?page=admin&subpage=bosses" class="nav-link <?=printClass("active", "admin", "bosses")?>">
                            <i class="nav-icon fas fa-pastafarianism text-fuchsia"></i>
                            <p>Boss</p>
                        </a>
                    </li>
                </ul>
            </li>
        <?php } ?>
        <?php if ($_SESSION["grade"] <= $_SESSION["Gestion"]) {?>
            <!--Api Discord ShortCut-->
            <li class="nav-item <?=printClass("menu-open", "discord")?>">
                <a href="#" class="nav-link <?=printClass("active", "discord")?>">
                    <i class="nav-icon fas fa-bell text-blue"></i>
                    <p>
                        Api
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="?page=discord&subpage=alarm" class="nav-link <?=printClass("active", "discord", "alarm")?>">
                            <i class="nav-icon fas fa-info text-blue"></i>
                            <p>rappel</p>
                        </a>
                    </li>
                </ul>
            </li>
        <?php } ?>
            <!--Raid-->
            <li class="nav-item <?=printClass("menu-open", "raid")?>">
                <a href="#" class="nav-link <?=printClass("active", "raid")?>">
                    <i class="nav-icon fas fa-skull text-red"></i>
                    <p>
                        Raid
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="?page=raid&subpage=info" class="nav-link <?=printClass("active", "raid", "info")?>">
                            <i class="nav-icon fas fa-info text-red"></i>
                            <p>Informations</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?page=raid&subpage=rank" class="nav-link <?=printClass("active", "raid", "rank")?>">
                            <i class="nav-icon fas fa-medal text-red"></i>
                            <p>Classement</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?page=raid&subpage=meteo" class="nav-link <?=printClass("active", "raid", "meteo")?>">
                            <i class="nav-icon fas fa-cloud-sun-rain text-red"></i>
                            <p>Météo</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?page=raid&subpage=followup" class="nav-link <?=printClass("active", "raid", "followup")?>">
                            <i class="nav-icon fas fa-list-ol text-red"></i>
                            <p>Tableau de Suivi</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?page=raid&subpage=miss" class="nav-link <?=printClass("active", "raid", "miss")?>">
                            <i class="nav-icon fas fa-user-slash text-red"></i>
                            <p>Les loupés <i class="fas fa-sad-cry"></i></p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?page=raid&subpage=summary" class="nav-link <?=printClass("active", "raid", "summary")?>">
                            <i class="nav-icon fas fa-th-list text-red"></i>
                            <p>Résumé des attaques</p>
                        </a>
                    </li>
        <?php if ($_SESSION["grade"] <= $_SESSION["Joueur"]) {?>
                    <li class="nav-item">
                        <a href="?page=raid&subpage=fightByGuild" class="nav-link <?=printClass("active", "raid", "fight")?>">
                            <i class="nav-icon fas fa-crosshairs text-red"></i>
                            <p>Attaques</p>
                        </a>
                    </li>
        <?php } ?>
                </ul>
            </li>
            <!--Conquête-->
            <li class="nav-item <?=printClass("menu-open", "conquest")?>">
                <a href="#" class="nav-link <?=printClass("active", "conquest")?>">
                    <i class="nav-icon fas fa-chess-knight text-orange"></i>
                    <p>
                        Conquête
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="?page=conquest&subpage=priority" class="nav-link <?=printClass("active", "conquest", "priority")?>">
                            <i class="nav-icon fas fa-chess-knight text-orange"></i>
                            <p>Plan de bataille</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?page=conquest&subpage=tremens" class="nav-link <?=printClass("active", "conquest", "tremens")?>">
                            <i class="nav-icon fas fa-chess-knight text-warning"></i>
                            <p>Strat Tremens</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?page=conquest&subpage=nocturnum" class="nav-link <?=printClass("active", "conquest", "nocturnum")?>">
                            <i class="nav-icon fas fa-chess-knight text-lightblue"></i>
                            <p>Strat Nocturnum</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?page=conquest&subpage=old" class="nav-link <?=printClass("active", "conquest", "old")?>">
                            <i class="nav-icon fas fa-chess-knight text-orange"></i>
                            <p>strat (old)</p>
                        </a>
                    </li>
                </ul>
            </li>
            <!--Membre-->
            <li class="nav-item <?=printClass("menu-open", "member")?>">
                <a href="#" class="nav-link <?=printClass("active", "member")?>">
                    <i class="nav-icon fas fa-users text-purple"></i>
                    <p>
                        Membres
                        <i class="right fas fa-angle-left"></i>
                        <?php if (!is_null($_SESSION["nbPending"])) { ?>
                        <span class="badge badge-danger right"><?=$_SESSION["nbPending"]?></span>
                        <?php } ?>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="?page=member&subpage=alliance" class="nav-link <?=printClass("active", "member", "alliance")?>">
                            <i class="nav-icon fas fa-list-ul text-purple"></i>
                            <p>Liste des membres</p>
                        </a>
                    </li>
                <?php if ($_SESSION["grade"] <= $_SESSION["Officier"]) {?>
                    <li class="nav-item">
                        <a href="?page=member&subpage=pending" class="nav-link <?=printClass("active", "member", "pending")?>">
                            <i class="nav-icon fas fa-clipboard-list text-purple"></i>
                            <p>En attente</p>
                            <span class="badge badge-danger right"><?=$_SESSION["nbPending"]?></span>
                        </a>
                    </li>
                <?php } ?>
                    <li class="nav-item">
                        <a href="?page=member&subpage=new" class="nav-link <?=printClass("active", "member", "new")?>">
                            <i class="nav-icon fas fa-plus text-purple"></i>
                            <p>Ajouter</p>
                        </a>
                    </li>
                </ul>
            </li>
            <!--Boss-->
            <li class="nav-item">
                <a href="?page=boss" class="nav-link <?=printClass("active", "boss")?>">
                    <i class="nav-icon fas fa-pastafarianism text-teal"></i>
                    <p>
                        Boss
                    </p>
                </a>
            </li>
            <li class="nav-item">
                <a href="?page=tip" class="nav-link">
                    <i class="nav-icon fas fa-question text-white"></i>
                    <p>
                        Astuces
                    </p>
                </a>
            </li>
            <!-- Discord -->
            <li class="nav-item">
                <a href="https://discord.gg/z33d8uZ" class="nav-link">
                    <i class="nav-icon fab fa-discord text-blue"></i>
                    <p>
                        Discord
                    </p>
                </a>
            </li>
        </ul>
    </nav>
    <!-- /.sidebar-menu -->
</div>

<?php $sidebar = ob_get_clean(); ?>

