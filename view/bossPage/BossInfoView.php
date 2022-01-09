<?php
function printColor($poucent) {
  if (is_null($poucent))
    return "default";
  elseif ($poucent >= 50.0)
    return "green";
  elseif ($poucent > 33.33)
    return "yellow";
  elseif ($poucent >= 25)
    return "pink";
  return "dark";
}

?>

<?php ob_start(); ?>
<div class="row">
<div class="col-lg-4 col-sm-6">
<img src="dist/image/bosses/<?=$v_boss->getId()?>.png"
     class="img-fluid img-thumbnail"
     style="padding: 0"
     width="200" height="200"
     alt="<?=$v_boss->getName()?>">
</div>
<?php foreach ($v_ailments as $ailmentId => $info) {
  $ailment = $info["ailment"];
  $weapons = $info["weapons"];
?>
<div class="col-lg-4 col-sm-6">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">
        <img src="dist/image/resources/ailment_<?=$ailmentId?>.png"
             class="img-fluid img-thumbnail"
             style="padding: 0"
             width="25" height="25"
             alt="<?=$ailment->getName()?>">
        <?= $ailment->getName() ?>
      </h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>#</th>
            <th>Arme</th>
            <th style="width: 40px">Dégâts</th>
          </tr>
        </thead>
        <tbody>
<?php foreach ($weapons as $wId => $weapon) { 
    $isEdit = !is_null($weapon->getRate());
?>
          <tr>
            <td>
              <?php if ($_SESSION["grade"] <= $_SESSION["Joueur"]) { ?>
                <button type="button" class="btn btn-default";
                        data-toggle="modal" 
                        data-target="#modalAll" 
                        data-title="<?= $weapon->getName() ?>"
                        data-id="<?= $isEdit ? $weapon->getAeId() : "" ?>"
                        data-weapon_id="<?= $weapon->getId() ?>"
                        data-img_id="<?= $weapon->getCharacInfo()["id"] ?>"
                        data-boss_id="<?= $isEdit ? $weapon->getBossInfo()["id"] : $v_boss->getId() ?>"
                        data-rate="<?= $isEdit ? $weapon->getRate() : "" ?>"
                        style="padding: 0"
                >
              <?php } ?>
                <img src="dist/image/heros/<?=$weapon->getCharacInfo()["id"]?>_weapon.png"
                  class="img-fluid img-thumbnail"
                  data-toggle="tooltip" title="<?=$weapon->getCharacInfo()["name"]?>"
                  style="padding: 0"
                  width="50" height="50"
                  alt="<?=$weapon->getName()?>">
              <?php if ($_SESSION["grade"] <= $_SESSION["Joueur"]) { ?>
                </button>
              <?php } ?>
            </td>
            <td><?=$weapon->getName()?></td>
            <td><span style="font-size:1.1rem;" class="badge bg-<?=printColor($weapon->getRate())?>"><?=$weapon->getRate() ?? "- "?>%</span></td>
          </tr>
<?php } ?>
        </tbody>
      </table>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</div>
<?php } ?>
</div>
<?php $content = ob_get_clean(); ?>

<?php if ($_SESSION["grade"] <= $_SESSION["Joueur"]) { ?>
<?php ob_start(); ?>
<div class="modal fade" id="modalAll">
  <div class="modal-dialog modal-sm">
    <div class="modal-content bg-default">
      <div class="modal-header bg-default">
        <img src="dist/image/heros/<?=$weapon->getId()?>_weapon.png"
          id="img_title_src"
          class="img-fluid img-thumbnail"
          style="padding: 0"
          width="50" height="50"
          alt="">
        <h4 class="modal-title">memberName:</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form class="form" role="form" actionpage="?page=boss&subpage=info&id=<?=$v_boss->getId()?>" method="post">
        <div class="modal-body">
          <input type="hidden" id="ailmentEnduranceIdFormId" name="ailmentEnduranceIdForm" value=""/>
          <input type="hidden" id="weapondIdFormId" name="weapondIdForm" value=""/>
          <input type="hidden" id="bossIdFormId" name="bossIdForm" value=""/>
          <div class="form-group">
              <label for="rateFormId">Résistance à la compétence d'arme</label>
              <input type="number"
                     id="rateFormId"
                     name="rateForm"
                     value=""
                     min=0
                     max=100
                     step="0.01"
              />
          </div>
        </div>
        <div class="modal-footer justify-content-between bg-secondary">
          <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
          <button type="submit"
                  class="btn btn-primary buttonSubmit float-right"
                  name="updateForm">Sauvegarder</button>
        </div>
      </form>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<?php $modal = ob_get_clean(); ?>
<?php } ?>

<?php ob_start(); ?>
<script type="text/javascript">
$(function () {
    $('#modalAll').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget); // Button that triggered the modal
      var modal = $(this);
      
      modal.find('.modal-title').text(button.data('title'));
      modal.find('#img_title_src').attr("src", 'dist/image/heros/'+button.data('img_id')+'_weapon.png');
      modal.find('#img_title_src').attr("alt", button.data('title'));
      modal.find('#ailmentEnduranceIdFormId').val(button.data('id'));
      modal.find('#weapondIdFormId').val(button.data('weapon_id'));
      modal.find('#bossIdFormId').val(button.data('boss_id'));
      modal.find('#rateFormId').val(button.data('rate'));
      setTimeout(function (){
          modal.find('#rateFormId').focus();
      }, 200);
    });
        
});
$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();
});
</script>
<?php $specScript = ob_get_clean(); ?>


<?php require('view/template.php');
