<?php
require('MemberControler.php');
require('controller/Profile/ProfileControler.php');

$controller = new MemberControler();
if (!isset($_GET['subpage'])) {return; }

if ($_GET['subpage'] == 'new') {
    if ($_SESSION["grade"] > $_SESSION["Officier"]) { header('Location: index.php?page=error&code=403'); }
    $_SESSION["url"][] = Array("print" => "Nouveau", "url" => "index.php?page=member&subpage=new");
    $isInsert = isset($_POST["addMemberForm"]);
    if ($isInsert) {
        $controller->submitMember(
            getVar($_POST, "nameForm"),
            getVar($_POST, "guildForm"),
            getVar($_POST, "dateStartForm"));
    }
    $controller->addMember();
    return;
} elseif ($_GET['subpage'] == 'delete') {
    if ($_SESSION["grade"] > $_SESSION["Officier"]) { header('Location: index.php?page=error&code=403'); }
    $isInsert = isset($_POST["addMemberForm"]);
    $controller->deleteMember(getVar($_GET, "id"));
    header('Location: index.php?page=member&subpage=alliance');
    return;
    
} elseif ($_GET['subpage'] == 'edit') {
    if ($_SESSION["grade"] > $_SESSION["Officier"]) { header('Location: index.php?page=error&code=403'); } //TODO ok si son propre id
    $_SESSION["url"][] = Array("print" => "Mise a jour", "url" => "index.php?page=member&subpage=edit");
    $memberId = getVar($_GET, "id");
    $isUpdate = isset($_POST["editMemberForm"]);
    if ($isUpdate) {
        $controller->saveMember(
            getVar($_POST, "idForm"),
            getVar($_POST, "nameForm"),
            getVar($_POST, "guildForm"),
            getVar($_POST, "dateStartForm"));
    }
    $controller->editMember($memberId);
    return;
} elseif ($_GET['subpage'] == 'team') {
    $memberId = getVar($_GET, "id");
    if (($_SESSION["grade"] > $_SESSION["Officier"])
        and ($_SESSION["id"] != $memberId)) { header('Location: index.php?page=error&code=403'); }
    if (!is_null($memberId)) {
        $isInsert = isset($_POST["team1AddForm"]);
        $isInsert += isset($_POST["team2AddForm"]);
        $isInsert += isset($_POST["team3AddForm"]);
        $isUpdate = isset($_POST["team1UpdateForm"]);
        $isUpdate += isset($_POST["team2UpdateForm"]);
        $isUpdate += isset($_POST["team3UpdateForm"]);

        if ($isInsert) {
            $controller->submitTeam(
                getVar($_POST, "memberForm"),
                getVar($_POST, "teamForm"),
                getVar($_POST, "hero1Form"),
                getVar($_POST, "hero2Form"),
                getVar($_POST, "hero3Form"),
                getVar($_POST, "hero4Form")
            );
        } elseif ($isUpdate) {
            $controller->saveTeam(
                getVar($_POST, "idForm"),
                getVar($_POST, "hero1Form"),
                getVar($_POST, "hero2Form"),
                getVar($_POST, "hero3Form"),
                getVar($_POST, "hero4Form")
            );
        }
        $controller->listTeam($memberId);
        return;
    }
} elseif ($_GET['subpage'] == 'pending') {
    if ($_SESSION["grade"] > $_SESSION["Officier"]) { header('Location: index.php?page=error&code=403'); }
    $_SESSION["url"][] = Array("print" => "Liste d'attente", "url" => "index.php?page=member&subpage=pending");
    $isSubmit = isset($_POST["linkAccount"]);
    $memberId = getVar($_POST, "memberForm");
    $pendingId = getVar($_POST,"pendingForm");

    $controller->linkMember($isSubmit, $memberId, $pendingId);
    return;
} elseif ($_GET['subpage'] == 'alliance') {
    $controller->listMembers();
    return;


// ---------------------------------------------------------------------------------------------------------------------
// --- Crew ------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
} elseif ($_GET['subpage'] == 'crew') {
    $memberId = getVar($_GET, "id");
    if ($memberId != $_SESSION["id"]) {
      (new ProfileControler())->printPage($memberId, false);
      exit();
    }
    $_SESSION["url"][] = Array("print" => "Liste des Perso", "url" => "index.php?page=member&subpage=crew");
    $viewPageForced = getVar($_GET, "view");
    
    $isPull = isset($_POST["pullHeroOrWeaponForm"]);
    $isUpdate = isset($_POST["updateForm"]);
    $isMlb = isset($_POST["isMlbForm"]);
    $f_id = getVar($_POST,"crewIdForm");
    $f_memberId = getVar($_POST,"memberIdForm");
    $f_charactId = getVar($_POST,"charactIdForm");
    $f_level = getVar($_POST,"levelForm");
    $f_evolved = getVar($_POST,"evolutionForm");
    $f_nbBreak = getVar($_POST,"breakForm");
    $f_hasWeapon = isset($_POST["weaponForm"]);
    $f_nbWeaponBreak = getVar($_POST,"weaponBreakForm");
    if ($isMlb) {
        $f_level = 83;
        $f_evolved = 5;
        $f_nbBreak = 5;
    }
    if ($isPull) {
       if ($_SESSION["grade"] > $_SESSION["Joueur"]) { header('Location: index.php?page=error&code=403'); exit();  }
       $controller->addCrew($f_memberId, $f_charactId, $f_level, $f_evolved, $f_nbBreak, $f_hasWeapon, $f_nbWeaponBreak);
    } else if ($isUpdate) {
       if ($_SESSION["grade"] > $_SESSION["Joueur"]) { header('Location: index.php?page=error&code=403'); exit();  }
       $controller->saveCrew($f_id, $f_memberId, $f_charactId, $f_level, $f_evolved, $f_nbBreak, $f_hasWeapon, $f_nbWeaponBreak);
    }
    $controller->printCrew($memberId, $viewPageForced);
    exit();
}
