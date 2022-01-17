<?php

function printActiveTab($param, $val) {
  return $param == $val ? "active" : "";
}
?>
<?php ob_start(); ?>
<div class="row">
<div class="col-md-3">
  <!-- Profile Image -->
  <div class="card card-purple card-outline">
    <div class="card-body box-profile">
      <div class="text-center">
        <img class="profile-user-img img-fluid img-circle"
             src="dist/image/default-user_profile.jpg"
             alt="User profile picture">
      </div>

      <h3 class="profile-username text-center"><?=$v_member->getName()?> (#<?=$v_member->getTag()?>) </h3>

  <?php if ($_SESSION["grade"] <= $_SESSION["Joueur"]) { ?>
      <p class="text-muted text-center"><?=$v_member->getPermInfo()["name"]?></p>
  <?php } ?>

      <ul class="list-group list-group-unbordered mb-3">
  <?php if ($v_member->getGuildInfo()["id"] != 0) { ?>
        <li class="list-group-item">
          <b>Chez <?=$v_member->getGuildInfo()["name"]?> depuis le</b>
          <a class="float-right"><?=strftime("%e %B %Y", strtotime($v_member->getDateStart()))?></a>
        </li>
  <?php } ?>
        <!--
        <li class="list-group-item">
          <b>Following</b> <a class="float-right">543</a>
        </li>
        <li class="list-group-item">
          <b>Friends</b> <a class="float-right">13,287</a>
        </li>
        -->
      </ul>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</div>
  
<div class="col-md-9">
  <div class="card">
    <div class="card-header p-2">
      <ul class="nav nav-pills">
        <li class="nav-item"><a class="nav-link <?=printActiveTab("heros", $v_activeTab)?>" href="#heros" data-toggle="tab">Liste Héros</a></li>
        <li class="nav-item"><a class="nav-link <?=printActiveTab("stats", $v_activeTab)?>" href="#stats" data-toggle="tab">Statistiques</a></li>
        <?php if ($v_isProfile) { ?>
          <li class="nav-item"><a class="nav-link <?=printActiveTab("settings", $v_activeTab)?>" href="#settings" data-toggle="tab">Paramètres</a></li>
          <li class="nav-item"><a class="nav-link <?=printActiveTab("notifs", $v_activeTab)?>" href="#notifs" data-toggle="tab">Notifs</a></li>
        <?php } ?>
      </ul>
    </div><!-- /.card-header -->
    <div class="card-body">
      <div class="tab-content">
        <div class="tab-pane <?=printActiveTab("heros", $v_activeTab)?>" id="heros">
          <table id="example1" class="table table-bordered table-hover">
            <thead>
              <tr>
                <th></th><!-- for primary sort only -->
                <th>Grade</th>
                <th>Elément</th>
                <th>Nom</th>
                <th>Evol</th>
                <th>Level</th>
                <th>Arme</th>
                <th>low break</th>
              </tr>
            </thead>
            <tbody>
      <?php foreach ($v_heros as $characId => $info) { 
            $charac = $info["charac"];
            $isLbWeapon = ($charac["hasWeapon"] and ($charac["nbWeaponBreak"] > 0));
            $printDFlex = $isLbWeapon ? "" : "d-flex";
            if (!$info["isPull"]) { continue; }
      ?>
              <tr style="background-color: <?=$charac["color"]["background"]?>">
                <td><?=$charac["grade"]["value"]?></td>
                <td><?=$charac["grade"]["name"]?></td>
                <td><?=$charac["element"]?></td>
                <td>
                  <img src="dist/image/heros/<?=$characId?>_icon.png"
                       class="img-fluid img-thumbnail"
                       width="50" height="50"
                       style="border: 2px solid <?=$charac["color"]["frame"]?>; padding: 0; background-color: #a56e957a"
                       alt="<?=$charac["name"]?>">
                  <?=$charac["name"]?>
                </td>
                <td><?=$charac["stars"]?></td>
                <td><?=$charac["level"]?></td>
                <td><?php if ($charac["hasWeapon"]) { ?>
                      <img src="dist/image/heros/<?=$characId?>_weapon.png"
                           class="img-fluid img-thumbnail"
                           width="50" height="50"
                           style="background-color: #00000000; border: 0; box-shadow: 0 0 0"
                           alt="<?=$charac["name"]?> weapon">
                    <?php } ?>
                </td>
                <td><?php if ($charac["hasWeapon"]) { echo $charac["nbWeaponBreak"]; } ?></td>
              </tr>
      <?php } ?>
            </tbody>
          </table>
        </div>
        <div class="tab-pane <?=printActiveTab("stats", $v_activeTab)?>" id="stats">
          statistiques en tout genre
        </div>
        <?php if ($v_isProfile) { ?>
          <div class="tab-pane <?=printActiveTab("settings", $v_activeTab)?>" id="settings">
            <form class="form-horizontal" role="form" actionpage="?page=profile" method="post">
              <input type="hidden"
                     name="idForm"
                     value="<?=$v_member->getId()?>">
              <div class="form-group row">
                <label for="nameFormId" class="col-sm-2 col-form-label">Nom (In Game)</label>
                <div class="col-sm-10">
                  <input type="text"
                         class="form-control"
                         id="nameFormId"
                         value="<?=$v_member->getName()?>"
                         disabled>
                </div>
              </div>
              <div class="form-group row">
                <label for="tagFormId" class="col-sm-2 col-form-label">Tag</label>
                <div class="col-sm-10">
                  <input type="text"
                         class="form-control"
                         id="tagFormId"
                         value="<?=$v_member->getTag()?>"
                         disabled>
                </div>
              </div>
              <div class="form-group row">
                <label for="loginFormId" class="col-sm-2 col-form-label">Login</label>
                <div class="col-sm-10">
                  <input type="text"
                         class="form-control"
                         id="loginFormId"
                         name="loginForm"
                         value="<?=$v_member->getLogin()?>">
                </div>
              </div>
              <div class="form-group row">
                <label for="oldPasswdFormId" class="col-sm-2 col-form-label">Mot de passe actuel</label>
                <div class="col-sm-10">
                  <input type="password"
                         class="form-control"
                         id="oldPasswdFormId"
                         name="oldPasswdForm">
                </div>
              </div>
              <div class="form-group row">
                <label for="newPasswdFormId" class="col-sm-2 col-form-label">Nouveau mot de passe<br>(si besoin de changer)</label>
                <div class="col-sm-10">
                  <input type="password"
                         class="form-control"
                         id="newPasswdFormId"
                         name="newPasswdForm">
                </div>
              </div>
              <div class="form-group row">
                <div class="offset-sm-2 col-sm-10">
                  <button type="submit" class="btn btn-primary" name="updateMemberForm">Sauvegarder</button>
                </div>
              </div>
            </form>
          </div>
          <div class="tab-pane <?=printActiveTab("notifs", $v_activeTab)?>" id="notifs">
            Notifications
          </div>
        <?php } ?>
      </div>
      <!-- /.tab-content -->
    </div><!-- /.card-body -->
  </div>
  <!-- /.card -->
</div>
<!-- /.col -->
</div>
<!-- /.row -->

<?php $content = ob_get_clean(); ?>

<?php ob_start(); ?>
<!-- DataTables  & Plugins -->
<script src="dist/AdminLTE-3.1.0/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="dist/AdminLTE-3.1.0/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="dist/AdminLTE-3.1.0/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="dist/AdminLTE-3.1.0/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="dist/AdminLTE-3.1.0/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="dist/AdminLTE-3.1.0/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="dist/AdminLTE-3.1.0/plugins/jszip/jszip.min.js"></script>
<script src="dist/AdminLTE-3.1.0/plugins/pdfmake/pdfmake.min.js"></script>
<script src="dist/AdminLTE-3.1.0/plugins/pdfmake/vfs_fonts.js"></script>
<script src="dist/AdminLTE-3.1.0/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="dist/AdminLTE-3.1.0/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="dist/AdminLTE-3.1.0/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
  $('.dataTables_length').addClass('bs-select');;
});
$(function () {
  <?php
      echo "var jsMemberId = ".($v_member->getId())."\n";
  ?>

  $("#example1").DataTable({
    "responsive": true,
    "lengthChange": true, pageLength: 10, lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
    "autoWidth": false,
    "searching": true,
    "scrollCollapse": true, "scrollY": "500px",
    "paging": true,// "paging": true, "pagingType": "simple",
    "order": [[ 0, "desc" ]],
    "columnDefs": [
            {
                "targets": [ 0 ],
                "visible": false,
                "searchable": false,
                orderData: [ 0, 4, 5, 7 ]
            }
        ],
    "buttons": ["copy", "csv", "excel", "pdf"
  <?php if ($v_isProfile) { echo
      ",{
          text: 'modifier Héros',
          action: function ( e, dt, node, config ) {
              location.href = '?page=member&subpage=crew&id='+jsMemberId;
          }
      }";
  } ?>
    ]

  }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
});
</script>
<?php $specScript = ob_get_clean(); ?>

<?php ob_start(); ?>
  <link rel="stylesheet" href="dist/AdminLTE-3.1.0/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="dist/AdminLTE-3.1.0/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="dist/AdminLTE-3.1.0/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<?php $specStyle = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
