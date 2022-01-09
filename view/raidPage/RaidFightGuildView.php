<?php ob_start(); ?>
<div class="row justify-content-between">
<div class="col-md-7">
<form action="?page=raid&subpage=fightByGuild" method="post" class="form-horizontal">
  <div class="form-group row align-items-center">
    <label for="dateFormId" class="col-sm-1 col-form-label">Date: </label>
    <div class="col-sm-3">
      <input type="date"
             name="dateForm"
             id="dateFormId"
             value="<?=$f_date?>"/>
    </div>
    <div class="col-sm-2">
      <button type="submit" class="btn btn-primary" name="chooseDate">Selectionner</button>
    </div>
  </div>
</form>
</div>
</div>
<div class="row">
<?php foreach ($v_fights as $memberId => $info) { 
      $memberName = $info["member"];
?>
  <div class="col-lg-4 col-sm-6 col-12">
    <div class="info-box">
      <div class="info-box-content col-6">
        <span class="info-box-text"><?= $memberName ?></span>
        <div team_nb="<?= $v_fights[$memberId]["teamIds"] ?>"
              <?php foreach($info["savedTeams"] as $nb => $team) {
                echo '
                  team_'.$nb.'="'.$team["id"].':'.$team["teamNumber"].':'.$team["heros"].'"
                  ';
              } ?>>
        </div>
      </div>
    <?php $fights = $v_fights[$memberId]["fights"];
//    foreach($fights as $i => $fight) {
    for ($i = 1; $i <= 3; $i++) {
    $isEdit = array_key_exists($i, $fights);
    if ($isEdit) { $fight = $fights[$i];}?>
      <div class="info-box-content col-2">
        <button type="button" class="btn btn-<?= $isEdit ? "default" : "primary"?>"';
                data-toggle="modal" 
                data-target="#modalAll" 
                data-is_edit="<?= $isEdit ? "true" : "false" ?>"
                data-id="<?= $isEdit ? $fight->getId() : "" ?>"
                data-guild_id="<?= $isEdit ? $fight->getGuildInfo()["id"] : $info["guildId"] ?>"
                data-raid_id="<?= $isEdit ? $fight->getRaidId() : $f_raid->getId() ?>"
                data-member_id="<?= $memberId ?>"
                data-member_name="<?= $memberName ?>"
                data-date="<?= $isEdit ? $fight->getDate() : $f_date ?>"
                data-team_number="<?= $i ?>"
                
                data-boss_id="<?= $isEdit ? $fight->getBossInfo()["id"] : "0" ?>"
                data-damage="<?= $isEdit ? $fight->getDamage() : "" ?>"
                data-team_hero1="<?= $isEdit ? $fight->getHero1Info()["id"] : "0" ?>"
                data-team_hero2="<?= $isEdit ? $fight->getHero2Info()["id"] : "0" ?>"
                data-team_hero3="<?= $isEdit ? $fight->getHero3Info()["id"] : "0" ?>"
                data-team_hero4="<?= $isEdit ? $fight->getHero4Info()["id"] : "0" ?>"
                <?php if($isEdit) { echo 'style="padding: 0"';} ?>
        >
          <?php if($isEdit) { ?>
          <img src="dist/image/bosses/<?=$fight->getBossInfo()["id"]?>.png"
               class="img-fluid img-thumbnail"
               data-toggle="tooltip" title="<?=number_format($fight->getDamage(), 0, ',', ' ')?>"
               style="padding: 0"
               alt="<?=$fight->getBossInfo()["id"]?>">
          <?php } else { ?>
          <i class="right fas fa-plus"></i>
          <?php } ?>
        </button>
      </div>
    <?php } ?>
    </div>
  </div>
<?php } ?>
</div>

<?php $content = ob_get_clean(); ?>

<?php function printHero($nb, $character, $elements) {
  echo '<select name="hero'.$nb.'Form"
                id="hero'.$nb.'FormId"
                class="custom-select form-control">';
    $prevElement = "0";
    $prevLevel = "0";
    $heroFunc = 'getHero'."1".'Info';//$heroNb
    echo "<option value=0></option>";
    foreach ($character as $charId => $charInfo) {
        $charName = ($charId == 0) ? $charInfo->getName() : $charInfo->getGrade()."* ".$charInfo->getName();
        if ($prevLevel != $charInfo->getGrade()) {
            $prevLevel = $charInfo->getGrade();
            echo "<optgroup label='Grade $prevLevel*'>";
        }
        if ($prevElement != $charInfo->getElementInfo()["id"]) {
            $prevElement = $charInfo->getElementInfo()["id"];
            echo '<optgroup label='.$elements[$prevElement].'>';
        }
        echo "<option value=$charId>$charName</option>";
    }
    echo '</optgroup>';
  echo '</select>';
} 

function printSavedTeam($nb, $heros) {
  $heros = explode("-", $heros);
  foreach ($heros as $unused => $heroNb) {    
    echo '
      <img src="dist/image/heros/'.$heroNb.'_icon.png"
          hero="'.($unused+1).'"
          class="img-fluid img-thumbnail hero team'.$nb.'"
          width="100" height="100"
          alt="'.$heroNb.'">
    ';
  }
} 
?>

<?php ob_start(); ?>
<div class="modal fade" id="modalAll">
  <div class="modal-dialog modal-xl">
    <div class="modal-content bg-default">
      <div class="modal-header bg-<?=$_SESSION['guild']['color']?>">
        <h4 class="modal-title">memberName:</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form class="form" role="form" actionpage="?page=raid&subpage=fightByGuild" method="post">
        <div class="modal-body">
          <input type="hidden" id="recorderIdFormId" name="recorderIdForm" value="<?= $_SESSION["id"] ?>"/>
          <input type="hidden" id="idFormId" name="idForm" value=""/>
          <input type="hidden" id="guildFormId" name="guildForm" value=""/>
          <input type="hidden" id="memberFormId" name="memberForm" value=""/>
          <input type="hidden" id="raidIdFormId" name="raidIdForm" value=""/>
          <input type="hidden" id="dateFormId" name="dateForm" value=""/>
          <input type="hidden" id="teamFormId" name="teamForm" value=""/>
          <input type="hidden" id="teamNbUsedFormId" name="teamNbUsedForm" value=""/>
          <div class="form-group row">
            <label for="bossFormId" class="col-sm-1 col-form-label">Boss: </label>
            <div class="col-sm-3">
              <select name="bossForm"
                      id="bossFormId"
                      class="custom-select form-control">
                <option value="0"></option>
                <?php for ($j = 1; $j <= 4; $j++) { $boss = "getBoss".$j."Info";
                  $sel = ($f_raid->$boss()["id"] == $bossSelected) ? "selected" : ""; ?>
                  <option value="<?=$f_raid->$boss()["id"]?>" <?=$sel?>><?=$f_raid->$boss()["name"]?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-sm-2">
              <input type="number"
                     class="form-control" 
                     id="damageFormId"
                     name="damageForm"
                     placeholder="Dégâts">
            </div>
            <button type="submit" class="btn btn-primary col-sm-2 buttonSubmit"></button>
          </div>
          <div class="form-group row align-items-center">
            <label for="hero1FormId" class="col-sm-1 col-form-label">Team: </label>
            <div class="col-sm-2">
              <?= printHero(1, $v_characters, $v_elements)?>
            </div>
            <div class="col-sm-2">
              <?= printHero(2, $v_characters, $v_elements)?>
            </div>
            <div class="col-sm-2">
              <?= printHero(3, $v_characters, $v_elements)?>
            </div>
            <div class="col-sm-2">
              <?= printHero(4, $v_characters, $v_elements)?>
            </div>
            <div class="col-sm-2">
              <div class="custom-control custom-switch" added="false">
                <!-- input checkbox -->
              </div>
            </div>
            <div class="col-sm-1">
              <button type="button"
                      class="btn btn-outline-secondary clean-team">
                <i class="right fas fa-brush"></i>
              </button>
            </div>
          </div>
          <div class="row">
          <?php for ($i = 0; $i <= 1; $i++) { ?>
          <div class="col-sm-6">
          <?php for ($j = $i*3+1; $j <= ($i+1)*3; $j++) { ?>
            <div class="form-group row align-items-center">
              <div class="col-sm-1">
                <i class="far fa-dot-circle "></i>
              </div>
              <div class="col-sm-11 select-team" id="select-team<?=$j?>">
                <?= printSavedTeam($j, "0-0-0-0") ?>
              </div>
            </div>
          <?php } ?>
          </div>
          <?php } ?>
          </div>
        </div>
        <div class="modal-footer justify-content-between bg-default">
          <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
          <button type="submit"
                  class="btn btn-primary buttonSubmit"
                  name="updateForm"></button>
          <button type="submit" class="btn btn-outline-danger float-right" name="teamDeleteForm" >Supprimer</button>
        </div>
      </form>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<?php $modal = ob_get_clean(); ?>

<?php ob_start(); ?>
<script type="text/javascript">
$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();
});
$(function () {
    $('#modalAll').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget); // Button that triggered the modal
      var modal = $(this);
      var memberName = button.data('member_name'); // Extract info from data-* attributes
      var teamNumber = button.data('team_number');
      modal.find('.modal-title').text(memberName + ' - Attaque ' + teamNumber);
      
      modal.find('#idFormId').val(button.data('id'));
      modal.find('#guildFormId').val(button.data('guild_id'));
      modal.find('#memberFormId').val(button.data('member_id'));
      modal.find('#raidIdFormId').val(button.data('raid_id'));
      modal.find('#dateFormId').val(button.data('date'));
      modal.find('#teamFormId').val(teamNumber);
      modal.find('#bossFormId').val(button.data('boss_id'));
      modal.find('#damageFormId').val(button.data('damage'));
      // 
      var valueHero1 = '#hero1FormId option[value="' + button.data('team_hero1') + '"]';
      $(valueHero1).prop('selected', true);
      var valueHero2 = '#hero2FormId option[value="' + button.data('team_hero2') + '"]';
      $(valueHero2).prop('selected', true);
      var valueHero3 = '#hero3FormId option[value="' + button.data('team_hero3') + '"]';
      $(valueHero3).prop('selected', true);
      var valueHero4 = '#hero4FormId option[value="' + button.data('team_hero4') + '"]';
      $(valueHero4).prop('selected', true);
      if (button.data('is_edit')) {
        modal.find('.buttonSubmit').attr("name" , 'teamUpdateForm');
        modal.find('.buttonSubmit').text('Modifier');
      } else {
        modal.find('.buttonSubmit').attr("name" , 'teamAddForm');
        modal.find('.buttonSubmit').text('Sauvegarder');
      }
      
      // set team saved
      var teamSaved = button.parent().parent().children("div").first().children("div");
      var teamSavedInfo = teamSaved.attr('team_nb');
      if (teamSavedInfo !== "") {
        $.each(teamSavedInfo.split('-'), function(index, value) {
          var teamInfo = teamSaved.attr('team_'+value).split(':');
          var heroId = teamInfo['2'].split('-');
          $('.hero.team'+value).each(function(index) {
            $( this ).attr("alt", heroId[index]);
            $( this ).attr("src", 'dist/image/heros/'+heroId[index]+'_icon.png');
          });
        });
      }
    });
    
    $('.select-team').click(function () {
      var teamNb = $(this).get(0).id.split("team")[1];
      var savedHeros = $(this).find('.hero');
      var switchButton = $('.custom-control.custom-switch');
      if (switchButton.attr('added') == 'false') {
        var input = '<input type="checkbox"'
                    +'class="custom-control-input"'
                    +'name="saveTeamForm"'
                    +'id="saveTeamFormId">';
        var label = '<label class="custom-control-label" for="saveTeamFormId">Save team ?</label>';
        switchButton.append(input);
        switchButton.append(label);
        switchButton.attr('added', 'true');
      }
      
      $('#teamNbUsedFormId').val(teamNb);
      savedHeros.each(function( index ) {
        var valueHero = '#hero'+(index+1)+'FormId option[value="' + $( this ).attr("alt") + '"]';
        $(valueHero).prop('selected', true);
      });
    });
    
    $('.clean-team').click(function () {
      var switchButton = $('.custom-control.custom-switch');
      switchButton.attr('added', false);
      switchButton.empty();
      
      $.each( [1,2,3,4], function( i, v ){
        var valueHero = '#hero'+v+'FormId option[value="0"]';
        $(valueHero).prop('selected', true);
      });
    });
});
</script>
<?php $specScript = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
