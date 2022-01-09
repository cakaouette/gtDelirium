<?php ob_start(); ?>
<div class="row">
  <div class="card col-lg-6">
    <div class="card-header">
      <h3 class="card-title">Classement Global</h3>
    </div>
    <div class="card-body">
      <table id="table-rankGlobal" class="table table-bordered table-hover">
        <thead>
          <tr>
            <th># alliance</th>
            <th># guilde</th>
            <th>Guilde</th>
            <th>Nom</th>
            <th>Moyenne</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($v_fights as $pseudoId => $infoPseudo) {
          $avg = round($infoPseudo["totalSum"]/$infoPseudo["totalCount"]);
          ?>
          <tr style="background-color: <?=$infoPseudo["guildColor"]?>">
            <td><?=$infoPseudo["rankAlliance"]?></td>
            <td><?=$infoPseudo["rankGuild"]?></td>
            <td><?=$infoPseudo["guildName"]?></td>
            <td><?=$infoPseudo["memberName"]?></td>
            <td><?=number_format($avg, 0, ',', ' ')?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card col-lg-6">
    <div class="card-header">
      <h3 class="card-title">Moyennes par boss</h3>
    </div>
    <div class="card-body">
      <table id="table-rankBoss" class="table table-bordered table-hover">
        <thead>
          <tr>
            <th># alliance</th>
            <th>rank boss 1</th>
            <th>rank boss 2</th>
            <th>rank boss 3</th>
            <th>rank boss 4</th>
            <th>Guilde</th>
            <th>Nom</th>
            <th>Boss</th>
            <th>ratio</th>
            <th>Dégâts</th>
            <th>sigma</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($v_fights as $pseudoId => $infoPseudo) {
          $avg = round($infoPseudo["totalSum"]/$infoPseudo["totalCount"]);
          ?>
          <?php foreach ($v_bosses as $bossId => $bossInfo) { 
            ?>
            <?php if (array_key_exists($bossId, $infoPseudo["bosses"]) ) { 
              $a_dam = $infoPseudo["bosses"][$bossId]["keptDamages"];
              $nb = 3*count($a_dam)/$infoPseudo["totalCount"];
              $avg_boss = $infoPseudo["bosses"][$bossId]["Average"];
              ?>
              <tr style="background-color: <?=$infoPseudo["guildColor"]?>">
                <td><?=$infoPseudo["rankAlliance"]?></td>
                <td><?=$infoPseudo["rankBoss1"] ?? 90?></td>
                <td><?=$infoPseudo["rankBoss2"] ?? 90?></td>
                <td><?=$infoPseudo["rankBoss3"] ?? 90?></td>
                <td><?=$infoPseudo["rankBoss4"] ?? 90?></td>
                <td><?=$infoPseudo["guildName"]?></td>
                <td><?=$infoPseudo["memberName"]?></td>
                <td><?=$bossInfo["name"]?></td>
                <td class="text-right"><?=number_format($nb, 3, ',', ' ')."x"?></td>
                <td class="text-right"><?=number_format($avg_boss, 0, ',', ' ')?></td>
                <td class="text-right"><?=number_format($infoPseudo["bosses"][$bossId]["Sigma"], 0, ',', ' ')?></td>
              </tr>
            <?php } ?>
          <?php } ?>
        <?php } ?>
        </tbody>
      </table>
    </div>
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
  $('.dataTables_length').addClass('bs-select');
});
$(function () {
  var tableGlobal = $("#table-rankGlobal").DataTable({
    "responsive": true,
    "lengthChange": false, //"lengthChange": true, pageLength: 5, lengthMenu: [10, 30, 20, 50, 100, 200, 500],
    "searching": true,
    "scrollCollapse": true, "scrollY": "500px",
    "paging": false,
    "columnDefs": [
            {
                "targets": [ 1 ],
                "visible": false
            }
    ],
    "buttons": [
        {
            text: 'All',
            action: function ( e, dt, node, config ) {
                tableGlobal.columns(0).visible(true);
                tableGlobal.columns(1).visible(false);
                tableGlobal.columns(2).visible(true);
                tableGlobal.search('').draw();
                tableBoss.columns(5).search('').draw();
            }
        },
        {
            text: 'Tremens',
            action: function ( e, dt, node, config ) {
                tableGlobal.columns(0).visible(false);
                tableGlobal.columns(1).visible(true);
                tableGlobal.columns(2).visible(false);
                tableGlobal.search(this.text()).draw();
                tableBoss.columns(5).search(this.text()).draw();
            }
        },
        {
            text: 'Nocturnum',
            action: function ( e, dt, node, config ) {
                tableGlobal.columns(0).visible(false);
                tableGlobal.columns(1).visible(true);
                tableGlobal.columns(2).visible(false);
                tableGlobal.search(this.text()).draw();
                tableBoss.columns(5).search(this.text()).draw();
            }
        },
        {
            text: 'Chill',
            action: function ( e, dt, node, config ) {
                tableGlobal.columns(0).visible(false);
                tableGlobal.columns(1).visible(true);
                tableGlobal.columns(2).visible(false);
                tableGlobal.search(this.text()).draw();
                tableBoss.columns(5).search(this.text()).draw();
            }
        }
      ]
  });
  tableGlobal.buttons().container().appendTo('#table-rankGlobal_wrapper .col-md-6:eq(0)');
    
  
  var tableBoss = $("#table-rankBoss").DataTable({
    "responsive": true,
    "lengthChange": false, //"lengthChange": true, pageLength: 5, lengthMenu: [10, 30, 20, 50, 100, 200, 500],
    "searching": true,
    "scrollCollapse": true, "scrollY": "500px",
    "paging": false,
    "columnDefs": [
            {
                "targets": [ 0, 1, 2, 3, 4, 5],
                "visible": false
            }
    ],
    "buttons": [
        {
            text: 'All',
            action: function ( e, dt, node, config ) {
                tableBoss.search('').draw();
                tableBoss.order([0, 'asc']);
                tableBoss.draw();
            }
        }
        <?php $i = 1;
        foreach ($v_bosses as $bossId => $bossInfo) { ?>
        ,{
            text: "<?=$bossInfo["shortName"]?>",
            action: function ( e, dt, node, config ) {
                tableBoss.search("<?=$bossInfo["name"]?>").draw();
                tableBoss.order([<?=$i++?>, 'asc']);
                tableBoss.draw();
            }
        }
        <?php } ?>
      ]
  });
  tableBoss.buttons().container().appendTo('#table-rankBoss_wrapper .col-md-6:eq(0)');
  
});
</script>
<?php $specScript = ob_get_clean(); ?>

<?php ob_start(); ?>
  <link rel="stylesheet" href="dist/AdminLTE-3.1.0/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="dist/AdminLTE-3.1.0/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="dist/AdminLTE-3.1.0/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<?php $specStyle = ob_get_clean(); ?>


<?php require('view/template.php'); ?>
