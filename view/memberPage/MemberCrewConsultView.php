<?php ob_start(); ?>
<?php if (($_SESSION["grade"] <= $_SESSION["Joueur"]) and $f_memberId == $_SESSION["id"]) { ?>
<div class="row">
  <div class="col-md-1">
    <a class="btn btn-secondary" href="?page=member&subpage=crew&id=<?=$_SESSION["id"]?>" role="button">Modification</a>
  </div>
  <div class="col-md-1 ml-auto">
    <a class="btn btn-secondary" href="?page=discord&subpage=helpCrew&id=<?=$_SESSION["id"]?>" role="button">Discord</a>
  </div>
</div>
<br>
<?php } ?>

<div class="row">
<div class="col-12">
<div class="card">
  <div class="card-body">
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
  <!-- /.card-body -->
</div>
<!-- /.card -->
</div>
</div>

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
  $("#example1").DataTable({
    "responsive": true,
    "lengthChange": false, //"lengthChange": true, pageLength: 5, lengthMenu: [10, 30, 20, 50, 100, 200, 500],
    "autoWidth": false,
    "searching": true,
    "paging": false,// "paging": true, "pagingType": "simple",
    "order": [[ 0, "desc" ]],
    "columnDefs": [
            {
                "targets": [ 0 ],
                "visible": false,
                "searchable": false,
                orderData: [ 0, 4, 5, 7 ]
            }
//            ,
//            {
//                "targets": [ 3 ],
//                "visible": false
//            }
        ],
    "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
  }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
//  $('#example2').DataTable({
//    "paging": true,
//    "lengthChange": false,
//    "searching": false,
//    "ordering": true,
//    "info": true,
//    "autoWidth": false,
//    "responsive": true,
//  });
});
</script>
<?php $specScript = ob_get_clean(); ?>

<?php ob_start(); ?>
  <link rel="stylesheet" href="dist/AdminLTE-3.1.0/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="dist/AdminLTE-3.1.0/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="dist/AdminLTE-3.1.0/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<?php $specStyle = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
