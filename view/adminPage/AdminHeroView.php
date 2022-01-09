<?php ob_start(); ?>
<div class="row">
<div class="col-md-6  ">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Liste des héros</h3>
    </div>
    <div class="card-body">
      <table id="table-heros" class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>Id</th>
            <th>Nom</th>
            <th>Grade</th>
            <th>Elément</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($v_characts as $id => $charact) { ?>
          <tr>
            <td><?=$charact->getId()?></td>
            <td><?=$charact->getName()?></td>
            <td><?=$charact->getGrade()?></td>
            <td><?=$charact->getElementInfo()["name"]?></td>
            <td>
              <div class="btn-group">
                <button type="button" class="btn btn-default">Action</button>
                <button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown">
                  <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu" role="menu">
                  <a class="dropdown-item" role="button"
                     data-toggle="modal" 
                     data-target="#modalHero"
                     edit="true"
                     data-id="<?=$charact->getId()?>"
                     data-name="<?=$charact->getName()?>"
                     data-grade="<?=$charact->getGrade()?>"
                     data-element_id="<?=$charact->getElementInfo()["id"]?>"
                  >
                    Modifier
                  </a>
                  <a class="dropdown-item" role="button">
                    Upload .png - TODO
                  </a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item bg-danger"
                     href="?page=admin&subpage=heros&delete=<?=$charact->getId()?>"
                  >
                    Supprimer
                  </a>
                </div>
              </div>
            </td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="col-md-6">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Liste des armes - TODO</h3>
    </div>
    <div class="card-body">
      <table id="table-weapons" class="table table-bordered table-hover">
        <thead>
          <tr>
            <th></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach (Array() as $id => $info) { ?>
          <tr>
            <td></td>
            <td></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>
<?php $content = ob_get_clean(); ?>

<?php ob_start(); ?>
<div class="modal fade" id="modalHero">
  <div class="modal-dialog modal-sm">
    <div class="modal-content bg-default">
      <div class="modal-header bg-<?=$_SESSION['guild']['color']?>">
        <h4 class="modal-title">Nouveau héro</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form class="form" role="form" actionpage="?page=admin&subpage=heros" method="post">
        <div class="modal-body">
          <input type="hidden" id="hero-idFormId" name="idForm" value=""/>
          <div class="form-group">
            <label for="hero-nameFormId">
              Nom
            </label>
            <input type="text"
                   class="form-control form-control-border"
                   name="nameForm"
                   id="hero-nameFormId"/>
          </div>
          <div class="form-group">
            <label for="hero-gradeFormId">Grade</label>
            <select name="gradeForm"
                    id="hero-gradeFormId"
                    class="custom-select form-control">
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3" selected>3</option>
            </select>
          </div>
          <div class="form-group">
            <label for="hero-elementIdFormId">Elément</label>
            <select name="elementIdForm"
                    id="hero-elementIdFormmId"
                    class="custom-select form-control">
              <option value="0"></option>
              <?php foreach ($v_elements as $id => $name) { ?>
                <option value="<?=$id?>"><?=$name?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="modal-footer justify-content-between bg-default">
          <button type="button" class="btn btn-primary float-right" data-dismiss="modal">Close</button>
          <button type="submit"
                  class="btn btn-primary buttonSubmit"
                  name="createHeroForm"
          >
            Ajouter
          </button>
        </div>
      </form>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<?php $modal = ob_get_clean(); ?>


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
  $("#table-heros").DataTable({
    "responsive": true,
    "lengthChange": false, //"lengthChange": true, pageLength: 5, lengthMenu: [10, 30, 20, 50, 100, 200, 500],
//    "autoWidth": false,
    "searching": true,
    "scrollCollapse": true, "scrollY": "500px",
    "paging": false,
    dom: 'Bfrtip',
      buttons: [
          {
              text: 'Ajouter',
              action: function ( e, dt, node, config ) {
                  $('#modalHero').modal('toggle');
              }
          }
      ]
  });
  
  $('#modalHero').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    if (button.attr('edit') == "true") {
      var modal = $(this);

      modal.find('.modal-title').text("Modification du héro " + button.data('id'));
      
      modal.find('#hero-idFormId').val(button.data('id'));
      modal.find('#hero-nameFormId').val(button.data('name'));

      var valueGrade = '#hero-gradeFormId option[value="' + button.data('grade') + '"]';
      $(valueGrade).prop('selected', true);
      var valueElementId = '#hero-elementIdFormmId option[value="' + button.data('element_id') + '"]';
      $(valueElementId).prop('selected', true);
      
      modal.find('.buttonSubmit').attr("name" , 'updateHeroForm');
      modal.find('.buttonSubmit').text('Modifier');
    }
  });
  
});
</script>
<?php $specScript = ob_get_clean(); ?>

<?php ob_start(); ?>
  <link rel="stylesheet" href="dist/AdminLTE-3.1.0/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="dist/AdminLTE-3.1.0/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="dist/AdminLTE-3.1.0/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<?php $specStyle = ob_get_clean(); ?>


<?php require('view/template.php'); ?>
