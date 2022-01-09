<?php ob_start(); ?>
<?php if (($_SESSION["grade"] <= $_SESSION["Joueur"]) and $f_memberId == $_SESSION["id"]) { ?>
<div class="row">
  <div class="col-md-1">
    <a class="btn btn-secondary" href="?page=profile" role="button">Consultation</a>
  </div>
</div>
<br>
<?php } ?>
<div class="row">
<?php
  $prevColor = NULL;
  foreach ($v_heros as $characId => $info) {
      $charac = $info["charac"];
      $isLbWeapon = ($charac["hasWeapon"] and ($charac["nbWeaponBreak"] > 0));
      $printDFlex = $isLbWeapon ? "" : "d-flex";
      $nextColor = $charac["color"]["background"];
      $closeDiv = false;
      if (!is_null($prevColor) and $nextColor != $prevColor) {
        $closeDiv = true;
        echo '</div><div class="row">';
      }
      $prevColor = $nextColor;
?>
    <div class="col-lg-3 col-md-5 col-sm-6 col-12">
        <div class="info-box" style="background-color: <?=$nextColor?>">
            <!--<span class="">Image du héro</span>-->
            <span class="info-box-icon">
            <?php if ($_SESSION["grade"] <= $_SESSION["Joueur"]) { ?>
                <a class="btn-lg btn-default" 
                   data-toggle="modal" 
                   data-target="#modal-<?=$characId?>" 
                   style="padding: 0">
            <?php } ?>
                <!--<i class="far fa-envelope"></i>-->
                    <img src="dist/image/heros/<?=$characId?>_icon.png"
                         class="img-fluid img-thumbnail"
                         style="border: 5px solid <?=$charac["color"]["frame"]?>; padding: 0; background-color: #a56e957a"
                         alt="<?=$charac["name"]?>">
           <?php if ($_SESSION["grade"] <= $_SESSION["Joueur"]) { ?>
                </a>
		   <?php } ?>
           </span>
            <?php if ($info["isPull"] or ($_SESSION["grade"] > $_SESSION["Joueur"])) { ?>
            <div class="info-box-content">
                <?php if ($charac["level"] == 0) { ?>
                    <span class="info-box-text">-</span>
                    <span class="info-box-text" >-</span>
                <?php } else { ?>
                    <span class="info-box-number">niv. <?=$charac["level"]?></span>
                    <span class="info-box-number" ><?=$charac["stars"]?> <i class="fa fa-star" style="color: <?=$charac["color"]["frame"]?>"></i></span>
                <?php } ?>
            </div>
            <div class="info-box-content">
                <?php if ($charac["nbBreak"] == 0) {$print = "-"; $isMlb = "text";}
                      else if ($charac["nbBreak"] == 5) {$print =  "Mlb"; $isMlb = "number";}
                      else {$print = "lb: ".$charac["nbBreak"]; $isMlb = "text";}?>
                <span class="info-box-<?=$isMlb?>"><?=$print?></span>
            </div>
            <div class="description-block border-left <?=$printDFlex?>" style="margin: 0">
                <?php if ($isLbWeapon) { ?>
                    <?php if ($charac["nbWeaponBreak"] < 5) {$print =  "+".$charac["nbWeaponBreak"]; $isMlb = "text";}
                          else {$print = "Mlb"; $isMlb = "number";}?>
                    <span class="info-box-<?=$isMlb?>"><?=$print?></span>
                <?php } ?>
                <span class="info-box-icon">
                    <?php if ($charac["hasWeapon"]) { ?>
                    <img src="dist/image/heros/<?=$characId?>_weapon.png"
                         class="img-fluid img-thumbnail"
                         style="background-color: #00000000; border: 0; box-shadow: 0 0 0"
                         alt="<?=$charac["name"]?> weapon">
                    <?php } ?>
                </span>
            </div>
            <?php } else { ?>
            <div class="info-box-content">
              <?php if ($_SESSION["id"] == $f_memberId) { ?>
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modal-<?=$characId?>">
                  ajouter
                </button>
              <?php } ?>
            </div>
            <?php } ?>
       </div>
    </div>
<?php } ?>
</div>

<?php $content = ob_get_clean(); ?>

<?php ob_start(); ?>
<?php foreach ($v_heros as $characId => $info) {
    $charac = $info["charac"];
    $isEdit = !is_null($charac["level"]);
?>
<div class="modal fade" id="modal-<?=$characId?>">
  <div class="modal-dialog">
    <div class="modal-content bg-default">
        <div class="modal-header bg-info">
          <h4 class="modal-title"><?=$charac["name"]?></h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form class="form" role="form" actionpage="?page=member&subpage=crew&id=<?=$f_memberId?>" method="post">
          <div class="modal-body">
              <input type="hidden" name="crewIdForm" value="<?=$charac["crewId"]?>"/>
              <input type="hidden" name="memberIdForm" value="<?=$f_memberId?>"/>
              <input type="hidden" name="charactIdForm" value="<?=$characId?>"/>
              <div class="form-group">
                  <div class="form-check">
                    <input type="checkbox"
                           class="form-check-input"
                           name="isMlbForm"
                           id="isMlbFormId"/>
                    <label class="form-check-label" for="isMlbFormId">Mlb ? (si sélectionné, pas besoin de remplir les 3 champs suivants)</label>
                  </div>
              </div>
              <div class="form-group">
                  <input type="number"
                         name="levelForm"
                         id="levelFormId"
                         <?php if ($isEdit) { echo "value='".$charac["level"]."'";}?>
                  />
                  <label for="levelFormId">Niveau</label>
              </div>
              <div class="form-group">
                  <input type="number"
                         name="evolutionForm"
                         id="evolutionFormId"
                         <?php if ($isEdit) { echo "value='".$charac["stars"]."'";}?>
                  />
                  <label for="evolutionFormId">Evolution</label>
              </div>
              <div class="form-group">
                  <input type="number"
                         name="breakForm"
                         id="breakFormId"
                         <?php if ($isEdit) { echo "value='".$charac["nbBreak"]."'";}?>
                  />
                  <label for="breakFormId">Rupture de limite</label>
              </div>
              <div class="form-group">
                  <div class="form-check">
                    <input type="checkbox"
                           class="form-check-input"
                           name="weaponForm"
                           id="weaponFormId"
                           <?php if ($charac["hasWeapon"]) { echo " checked";}?>/>
                    <label class="form-check-label" for="weaponFormId">Possède l'arme exclusive ?</label>
                  </div>
              </div>
              <div class="form-group">
                  <input type="number"
                         name="weaponBreakForm"
                         id="weaponBreakFormId"
                         <?php if ($isEdit) { echo "value='".$charac["nbWeaponBreak"]."'";}?>
                  />
                  <label for="weaponBreakFormId">Rupture de limite de l'arme</label>
              </div>
          </div>
          <div class="modal-footer justify-content-between bg-secondary">
            <button type="button" class="btn btn-outline-light" data-dismiss="modal">Close</button>
            <?php if($isEdit) { ?>
                <button type="submit" class="btn btn-outline-light" name="updateForm">Sauvegarder</button>
            <?php } else { ?>
                <button type="submit" class="btn btn-outline-light" name="pullHeroOrWeaponForm">Ajouter</button>
            <?php } ?>
          </div>
        </form>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<?php } ?>
<?php $modal = ob_get_clean(); ?>


<?php require('view/template.php'); ?>
