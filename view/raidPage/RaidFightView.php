<?php
function printSelectHero($teamNb, $heroNb, $characters, $elements) {
    $prevElement = "0";
    $prevLevel = "0";
    $heroFunc = 'getHero'.$heroNb.'Info';
    foreach ($characters as $charId => $charInfo) {
        $charName = ($charId == 0) ? $charInfo->getName() : $charInfo->getGrade()."* ".$charInfo->getName();
        if ($prevLevel != $charInfo->getGrade()) {
            $prevLevel = $charInfo->getGrade();
            echo "<optgroup label='Grade $prevLevel*'>";
        }
        if ($prevElement != $charInfo->getElementInfo()["id"]) {
            $prevElement = $charInfo->getElementInfo()["id"];
            echo '<optgroup label='.$elements[$prevElement].'>';
        }
        $sel = ($charId == $teamNb->$heroFunc()["id"]) ? "selected" : "";
        echo "<option value=$charId $sel>$charName</option>";
    }
    echo '</optgroup>';
}

function computeInProgressFight($member, $t1, $t2, $t3) {
    if (is_null($member)) {
        return "";
    }
    $nbFight = 0;
    for ($i = 1; $i <= 3; $i++) {
        $team = 't'.$i;
        $nbFight += (!is_null($$team) and ($$team->getId() != 0)) ? 1 : 0;
    }
    $icon = "fa-spinner";
    $col = "purple";
    if ($nbFight == 0) {
        $icon = "fa-ban";
        $col = "danger";
    } elseif ($nbFight == 3) {
        $icon = "fa-check";
        $col = "success";
    }
    return "<div class='text-$col'>$nbFight/3<i class='icon fas $icon float-right' ></i></div>";
}
?>

<?php ob_start(); ?>
<p>Choisissez un joueur et une date</p>
<form action="?page=raid&subpage=fightByMember" method="post" class="form-horizontal">
  <div class="col-md-7">
    <div class="form-group row align-items-center">
      <div class="col-sm-1">
          
            <?=computeInProgressFight($f_memberId, $f_team1, $f_team2, $f_team3)?>
          
      </div>
      <label for="memberFormId" class="col-sm-2 col-form-label">Joueur: </label>
      <div class="col-sm-3">
        <select name="memberForm"
                id="memberFormId"
                class="custom-select form-control-border border-width-2">
          <?php foreach ($v_members as $memberId => $member) { 
            $sel = ($member->getId() == $f_memberId) ? "selected" : ""; ?>
            <option value="<?=$member->getId()?>" <?=$sel?>><?=$member->getName()?></option>
          <?php } ?>
        </select>
      </div>
      <label for="dateFormId" class="col-sm-1 col-form-label">Date: </label>
      <div class="col-sm-3">
        <input type="date"
               name="dateForm"
               id="dateFormId"
               value="<?=$f_date?>"/>
      </div>
      <div class="col-sm-2">
        <button type="submit" class="btn btn-primary" name="chooseForm">Selectionner</button>
      </div>
    </div>
  </div>
</form>
<?php if (!is_null($f_memberId)) { 
for ($i = 1; $i <= 3; $i++) { $team = 'f_team'.$i; ?>
<div class="card card-<?php echo $$team->getId() != 0 ? $_SESSION["guild"]["color"] : "secondary";?>">
  <div class="card-header">
    <h3 class="card-title">Team <?=$i?></h3>
  </div>
  <form class="form-horizontal" action="?page=raid&subpage=fight" method="post">
    <div class="card-body">
        <input type="hidden" name="recorderIdForm" value="<?= $_SESSION["id"] ?>"/>
        <input type="hidden" name="idForm" value="<?= $$team->getId() ?>"/>
        <input type="hidden" name="guildForm" value="<?= $$team->getGuildInfo()["id"] ?>"/>
        <input type="hidden" name="memberForm" value="<?= $$team->getPseudoInfo()["id"] ?>"/>
        <input type="hidden" name="dateForm" value="<?= $$team->getDate() ?>"/>
        <input type="hidden" name="teamForm" value="<?= $$team->getTeamNumber() ?>"/>
      <div class="form-group row">
        <label for="bossFormId" class="col-sm-1 col-form-label">Boss: </label>
        <div class="col-sm-3">
          <select name="bossForm"
                  id="bossFormId"
                  class="custom-select form-control">
            <?php $bossSelected = $$team->getBossInfo()["id"];?>
            <option value="0" <?php echo ($bossSelected == 0) ? "selected" : ""; ?>></option>
            <?php for ($j = 1; $j <= 4; $j++) { $boss = "getBoss".$j."Info";
              $sel = ($f_raid->$boss()["id"] == $bossSelected) ? "selected" : ""; ?>
              <option value="<?=$f_raid->$boss()["id"]?>" <?=$sel?>><?=$f_raid->$boss()["name"]?></option>
            <?php } ?>
          </select>
        </div>
        <div class="col-sm-2">
          <input type="number"
                 class="form-control" 
                 name="damageForm"
                 id="loginFormId"
                 placeholder="Dégâts"
                 <?php if ($$team->getId()) { $val=$$team->getDamage(); echo "value='$val'";}?>>
        </div>
        <?php $val = ($$team->getId() == 0) ? "team1AddForm" : "team1UpdateForm";
              $print = ($$team->getId() == 0) ? "Sauvegarder" : "Modifier"; ?>
        <button type="submit" class="btn btn-primary col-sm-2" name="<?=$val?>" ><?=$print?></button>
      </div>
      <div class="form-group row align-items-center">
        <label for="hero1FormId" class="col-sm-1 col-form-label">Team: </label>
        <div class="col-sm-2">
          <select name="hero1Form"
                  id="hero1FormId"
                  class="custom-select form-control">
            <?php echo printSelectHero($$team,1, $v_characters, $v_elements); ?>
          </select>
        </div>
        <div class="col-sm-2">
          <select name="hero2Form"
                  class="custom-select form-control">
            <?php echo printSelectHero($$team,2, $v_characters, $v_elements); ?>
          </select>
        </div>
        <div class="col-sm-2">
          <select name="hero3Form"
                  class="custom-select form-control">
            <?php echo printSelectHero($$team,3, $v_characters, $v_elements); ?>
          </select>
        </div>
        <div class="col-sm-2">
          <select name="hero4Form"
                  class="custom-select form-control">
            <?php echo printSelectHero($$team,4, $v_characters, $v_elements); ?>
          </select>
        </div>
        <div class="col-sm-2">
          <div class="custom-control custom-switch">
            <input type="checkbox"
                   class="custom-control-input"
                   name="saveTeamForm"
                   id="saveTeam<?=$i?>FormId"
                   <?php if ($$team->getHero1Info()["id"] == 0) { echo " checked";}?> >
            <label class="custom-control-label" for="saveTeam<?=$i?>FormId">Save team ?</label>
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer">
      <button type="submit" class="btn btn-primary float-left" name="<?=$val?>" ><?=$print?></button>
      <button type="submit" class="btn btn-secondary float-right" name="team1DeleteForm" >Supprimer</button>
    </div>
  </form>
</div>
<?php }} ?>
<?php $content = ob_get_clean(); ?>

<?php ob_start(); ?>
<!-- Tempusdominus Bootstrap 4 -->
<!--<link rel="stylesheet" href="../../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">-->
<?php $specStyle = ob_get_clean(); ?>

<?php ob_start(); ?>
<!-- Tempusdominus Bootstrap 4 -->
<!--<script src="dist/AdminLTE-3.1.0/plugins/moment/moment.min.js"></script>
<script src="dist/AdminLTE-3.1.0/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script type="text/javascript">
    $(function () {
        //Date picker
        $('#reservationdate').datetimepicker({
            format: 'L',
            locale: 'fr'
        });
        $('#dateFormId').datetimepicker({
            format: 'L'
        });
    });
</script>-->
<!-- bs-custom-file-input -->
<!--<script src="dist/AdminLTE-3.1.0/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>-->
<?php $specScript = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
