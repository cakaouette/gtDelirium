<?php
include_once('RaidControler.php');
include_once('controller/Member/MemberControler.php');

$guildId = getVar($_POST, "guildForm", "0");
$guildId = $guildId == "0" ? $_SESSION["guild"]["id"] : $guildId;
$raidId = getVar($_POST, "raidForm", $_SESSION["raidInfo"]["id"]);

if (!isset($_GET['subpage'])) { return; }

// ---------------------------------------------------------------------------------------------------------------------
// --- INFO ------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
if ($_GET['subpage'] == 'info') {
    $_SESSION["url"][] = Array("print" => "Informations", "url" => "index.php?page=raid&subpage=info");
    $controller = new RaidControler($guildId);
    if (is_null(getVar($_POST, "raidForm")) and $_SESSION["raidInfo"]["isFinished"]) {
      $controller->info($guildId, $raidId, $_SESSION["raidPreview"]["id"]);
    } else {
      $controller->info($guildId, $raidId, NULL);
    }
    return;
    
// ---------------------------------------------------------------------------------------------------------------------
// --- FIGHTS (old) ----------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
} elseif ($_GET['subpage'] == 'fightByMember') {
    $_SESSION["url"][] = Array("print" => "Attaques", "url" => "index.php?page=raid&subpage=fightByMember");
    if ($_SESSION["grade"] > $_SESSION["Benevole"]) { header('Location: index.php?page=error&code=403'); exit();};
    $isChoose = isset($_POST["chooseForm"]);
    $isInsert  = isset($_POST["team1AddForm"]);
    $isInsert += isset($_POST["team2AddForm"]);
    $isInsert += isset($_POST["team3AddForm"]);
    $isUpdate  = isset($_POST["team1UpdateForm"]);
    $isUpdate += isset($_POST["team2UpdateForm"]);
    $isUpdate += isset($_POST["team3UpdateForm"]);
    $isTeamSaved = getVar($_POST, "saveTeamForm");
    $isDeleted  = isset($_POST["team1DeleteForm"]);
    $isDeleted += isset($_POST["team2DeleteForm"]);
    $isDeleted += isset($_POST["team3DeleteForm"]);

    $memberId = getVar($_POST,"memberForm");
    $date = getVar($_POST,"dateForm", date("Y-m-d"));
    $controller = new RaidControler();
    if ($isChoose) {
        if (!$controller->checkDate($date)) {
            $memberId = NULL;
        }
    }
    if (is_null($memberId)) {
        $memberId = NULL;
        $date = date("Y-m-d");
    }
    if ($isInsert or $isUpdate) {
        if (!$controller->checkFight(getVar($_POST, "bossForm"), getVar($_POST, "damageForm"))) {
            $isInsert = false;
            $isUpdate = false;
        }
    }
    if ($isInsert) {
        $boss = getVar($_POST, "bossForm");
        $hero1 = getVar($_POST, "hero1Form");
        $hero2 = getVar($_POST, "hero2Form");
        $hero3 = getVar($_POST, "hero3Form");
        $hero4 = getVar($_POST, "hero4Form");
        $controller->submitFight(
            getVar($_POST, "recorderIdForm"),
            getVar($_POST, "guildForm"),
            getVar($_POST, "memberForm"),
            getVar($_POST, "dateForm"),
            getVar($_POST, "teamForm"),
            $boss == 0 ? NULL : $boss,
            getVar($_POST, "damageForm"),
            $hero1 == 0 ? NULL : $hero1,
            $hero2 == 0 ? NULL : $hero2,
            $hero3 == 0 ? NULL : $hero3,
            $hero4 == 0 ? NULL : $hero4
        );
    } elseif ($isUpdate or $isDeleted) {
        $boss = $isUpdate ? getVar($_POST, "bossForm") : 0;
        $damage = $isUpdate ? getVar($_POST, "damageForm") : NULL;
        $hero1 = getVar($_POST, "hero1Form");
        $hero2 = getVar($_POST, "hero2Form");
        $hero3 = getVar($_POST, "hero3Form");
        $hero4 = getVar($_POST, "hero4Form");
        $deleted = $isDeleted ? 1 : 0;
        $controller->saveFight(
            getVar($_POST, "recorderIdForm"),
            getVar($_POST, "idForm"),
            getVar($_POST, "guildForm"),
            getVar($_POST, "memberForm"),
            getVar($_POST, "dateForm"),
            getVar($_POST, "teamForm"),
            $boss == 0 ? NULL : $boss,
            $damage,
            $hero1 == 0 ? NULL : $hero1,
            $hero2 == 0 ? NULL : $hero2,
            $hero3 == 0 ? NULL : $hero3,
            $hero4 == 0 ? NULL : $hero4,
            $deleted
        );
    }
    if ($isTeamSaved == "on") {
        $c = new MemberControler();
        $c->addOrUpdateTeam(
            getVar($_POST, "memberForm"),
            getVar($_POST, "teamForm"),
            getVar($_POST, "hero1Form"),
            getVar($_POST, "hero2Form"),
            getVar($_POST, "hero3Form"),
            getVar($_POST, "hero4Form")
        );
    }
    $controller->addFight($guildId, $memberId, $date);
    return;
    
// ---------------------------------------------------------------------------------------------------------------------
// --- FIGHTS ----------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
} elseif ($_GET['subpage'] == 'fightByGuild') {
    $_SESSION["url"][] = Array("print" => "Attaques de la guilde", "url" => "index.php?page=raid&subpage=fightByGuild");
    if ($_SESSION["grade"] > $_SESSION["Joueur"]) {
      $_SESSION["redirectUrl"] = "?page=raid&subpage=fightByGuild";
      header('Location: index.php?page=connect');
      exit();
    }
    $controller = new RaidControler();

    $isSelectDate = isset($_POST["chooseDate"]);
    $date = getVar($_POST,"dateForm", date("Y-m-d"));
    if ($isSelectDate) {
        if (!$controller->checkDate($date)) {
            $date = date("Y-m-d");
        }
    }

    $isInsert  = isset($_POST["teamAddForm"]);
    $isUpdate  = isset($_POST["teamUpdateForm"]);
    $isDeleted  = isset($_POST["teamDeleteForm"]);
    $isTeamSaved = getVar($_POST, "saveTeamForm");

    if ($isInsert or $isUpdate) {
        if (!$controller->checkFight(getVar($_POST, "bossForm"), getVar($_POST, "damageForm"))) {
            $isInsert = false;
            $isUpdate = false;
        }
    }
    $hasError = false;
    if ($_SESSION["grade"] == $_SESSION["Joueur"] 
            and ($isInsert or $isUpdate or $isDeleted)
            and ($_SESSION["id"] != getVar($_POST, "recorderIdForm"))) {
      $hasError = true;
    } elseif (($isInsert or $isUpdate or $isDeleted)
            and (strtotime(getVar($_POST, "dateForm")) < strtotime($_SESSION["raidInfo"]["dateRaid"]))) {
      $hasError = true;
    } else {
      // Ok on pas faire les mise à jour bdd
      if ($isInsert) {
        $boss = getVar($_POST, "bossForm");
        $hero1 = getVar($_POST, "hero1Form");
        $hero2 = getVar($_POST, "hero2Form");
        $hero3 = getVar($_POST, "hero3Form");
        $hero4 = getVar($_POST, "hero4Form");
        $controller->submitFight(
            getVar($_POST, "recorderIdForm"),
            getVar($_POST, "guildForm"),
            getVar($_POST, "memberForm"),
            getVar($_POST, "raidIdForm"),
            getVar($_POST, "dateForm"),
            getVar($_POST, "teamForm"),
            $boss == 0 ? NULL : $boss,
            getVar($_POST, "damageForm"),
            $hero1 == 0 ? NULL : $hero1,
            $hero2 == 0 ? NULL : $hero2,
            $hero3 == 0 ? NULL : $hero3,
            $hero4 == 0 ? NULL : $hero4
        );
      } elseif ($isUpdate or $isDeleted) {
          $boss = $isUpdate ? getVar($_POST, "bossForm") : 0;
          $damage = $isUpdate ? getVar($_POST, "damageForm") : NULL;
          $hero1 = getVar($_POST, "hero1Form");
          $hero2 = getVar($_POST, "hero2Form");
          $hero3 = getVar($_POST, "hero3Form");
          $hero4 = getVar($_POST, "hero4Form");
          $deleted = $isDeleted ? 1 : 0;
          $controller->saveFight(
              getVar($_POST, "recorderIdForm"),
              getVar($_POST, "idForm"),
              getVar($_POST, "guildForm"),
              getVar($_POST, "memberForm"),
              getVar($_POST, "raidIdForm"),
              getVar($_POST, "dateForm"),
              getVar($_POST, "teamForm"),
              $boss == 0 ? NULL : $boss,
              $damage,
              $hero1 == 0 ? NULL : $hero1,
              $hero2 == 0 ? NULL : $hero2,
              $hero3 == 0 ? NULL : $hero3,
              $hero4 == 0 ? NULL : $hero4,
              $deleted
          );
      }
      if ($isTeamSaved == "on") {
          $c = new MemberControler();
          $c->addOrUpdateTeam(
              getVar($_POST, "memberForm"),
              getVar($_POST, "teamNbUsedForm"),
              getVar($_POST, "hero1Form"),
              getVar($_POST, "hero2Form"),
              getVar($_POST, "hero3Form"),
              getVar($_POST, "hero4Form")
          );
      }
    }
    $filter = NULL;
    if ($_SESSION["grade"] == $_SESSION["Joueur"]) {
      $filter = $_SESSION["id"];
    }

    $controller->printGuildFight($guildId, $date, $filter, $hasError);
    exit();
    
// ---------------------------------------------------------------------------------------------------------------------
// --- LISTE DERNIER INSERT SQL ----------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
} elseif ($_GET['subpage'] == 'summary') {
    $_SESSION["url"][] = Array("print" => "Résumé des attaques", "url" => "index.php?page=boss&subpage=summary");
    $controller = new RaidControler();
    $controller->listFight();
    return;
    
// ---------------------------------------------------------------------------------------------------------------------
// --- METEO -----------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
} elseif ($_GET['subpage'] == 'meteo') {
    $_SESSION["url"][] = Array("print" => "Météo", "url" => "index.php?page=boss&subpage=meteo");
    $controller = new RaidControler();
    $controller->meteoFight($guildId);
    return;
    
// ---------------------------------------------------------------------------------------------------------------------
// --- SUIVIS SCORES ---------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
} elseif ($_GET['subpage'] == 'followup') {
    $_SESSION["url"][] = Array("print" => "Tableau de suivi", "url" => "index.php?page=raid&subpage=followup");
    $controller = new RaidControler();
    $controller->followupFight($guildId);
    return;
    
// ---------------------------------------------------------------------------------------------------------------------
// --- SUIVIS MISS -----------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
} elseif ($_GET['subpage'] == 'miss') {
    $_SESSION["url"][] = Array("print" => "Les loupés", "url" => "index.php?page=raid&subpage=miss");
    $controller = new RaidControler();
    $controller->missedFight($guildId);
    return;
    
// ---------------------------------------------------------------------------------------------------------------------
// --- CLASSEMENT ------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
} elseif ($_GET['subpage'] == 'rank') {
    $_SESSION["url"][] = Array("print" => "Classement", "url" => "index.php?page=raid&subpage=rank");
    $controller = new RaidControler();
    $controller->rank($_SESSION["raidInfo"]["id"]);
    return;
}
