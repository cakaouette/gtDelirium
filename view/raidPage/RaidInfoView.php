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
<form action="?page=raid&subpage=info" method="post" class="form-horizontal">
  <div class="col-md-6">
    <div class="form-group row">
      <label for="guildFormId" class="col-sm-2 col-form-label">Guilde</label>
      <div class="col-sm-3">
        <select name="guildForm"
                id="guildFormId"
                class="custom-select form-control-border border-width-2">
          <option value="0"></option>
          <?php foreach ($v_guilds as $guild) { 
              $sel = ($guild->getId() == $f_guildId) ? "selected" : ""; ?>
          <option value="<?=$guild->getId()?>" <?=$sel?>><?=$guild->getName()?></option>
          <?php } ?>
        </select>
      </div>
      <label for="raidFormId" class="col-sm-2 col-form-label">Date de raid</label>
      <div class="col-sm-3">
        <select name="raidForm"
                id="raidFormId"
                class="custom-select form-control-border border-width-2">
          <?php foreach ($v_raids as $raid) { 
              $sel = ($raid->getId() == $f_raidId) ? "selected" : ""; ?>
          <option value="<?=$raid->getId()?>" <?=$sel?>><?=$raid->getDate()?></option>
          <?php } ?>
        </select>
      </div>
      <div class="col-sm-2">
        <button type="submit" class="btn btn-info" name="resetGuild">Selectionner</button>
      </div>
    </div>
  </div>
</form>
<div class="row">
  <div class="col-lg-6">
    <div class="card-body table-responsive p-0">
    <table class="table table-head-fixed table-hover">
      <tr>
        <th>Nom</th>
        <th>Type</th>
      </tr>
      <?php
      for ($i = 1; $i <= 4; $i++) { $boss = "getBoss".$i."Info"?>
      <tr>
        <td><?=$v_raidInfo->$boss()["name"]?></td>
        <td><?=$v_raidInfo->$boss()["e_name"]?></td>
      </tr>
      <?php } ?>
    </table>
    </div>
  </div>
<?php foreach ($v_ailments as $ailmentId => $info) {
  $ailment = $info["ailment"];
  $weapons = $info["weapons"];
?>
  <div class="col-lg-6">
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
              <th>
                <img src="dist/image/bosses/<?=$v_raidInfo->getBoss1Info()["id"]?>.png"
                     class="img-fluid img-thumbnail"
                     style="padding: 0"
                     width="75" height="75"
                     alt="<?=$v_raidInfo->getBoss1Info()["name"]?>">
              </th>
              <th>
                <img src="dist/image/bosses/<?=$v_raidInfo->getBoss2Info()["id"]?>.png"
                     class="img-fluid img-thumbnail"
                     style="padding: 0"
                     width="75" height="75"
                     alt="<?=$v_raidInfo->getBoss2Info()["name"]?>">
              </th>
              <th>
                <img src="dist/image/bosses/<?=$v_raidInfo->getBoss3Info()["id"]?>.png"
                     class="img-fluid img-thumbnail"
                     style="padding: 0"
                     width="75" height="75"
                     alt="<?=$v_raidInfo->getBoss3Info()["name"]?>">
              </th>
              <th>
                <img src="dist/image/bosses/<?=$v_raidInfo->getBoss4Info()["id"]?>.png"
                     class="img-fluid img-thumbnail"
                     style="padding: 0"
                     width="75" height="75"
                     alt="<?=$v_raidInfo->getBoss4Info()["name"]?>">
              </th>
            </tr>
          </thead>
          <tbody>
<?php foreach ($weapons as $wId => $weapon) { 
  $rate1 = $weapon->getRates()["rate1"];
  $rate2 = $weapon->getRates()["rate2"];
  $rate3 = $weapon->getRates()["rate3"];
  $rate4 = $weapon->getRates()["rate4"];
?>
            <tr>
              <td>
                <img src="dist/image/heros/<?=$weapon->getCharacInfo()["id"]?>_weapon.png"
                  class="img-fluid img-thumbnail"
                  data-toggle="tooltip" title="<?=$weapon->getCharacInfo()["name"]?>"
                  style="padding: 0"
                  width="50" height="50"
                  alt="<?=$weapon->getName()?>">
              </td>
              <td>
                <?=$weapon->getName()?>
              </td>
              <td><span style="font-size:1.1rem;" class="badge bg-<?=printColor($rate1)?>"><?=$rate1 ?? "- "?>%</span></td>
              <td><span style="font-size:1.1rem;" class="badge bg-<?=printColor($rate2)?>"><?=$rate2 ?? "- "?>%</span></td>
              <td><span style="font-size:1.1rem;" class="badge bg-<?=printColor($rate3)?>"><?=$rate3 ?? "- "?>%</span></td>
              <td><span style="font-size:1.1rem;" class="badge bg-<?=printColor($rate4)?>"><?=$rate4 ?? "- "?>%</span></td>
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

<?php ob_start(); ?>
<script type="text/javascript">
$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();
});
</script>
<?php $specScript = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
