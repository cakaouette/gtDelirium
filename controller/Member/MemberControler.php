<?php
include_once('controller/AbstractControler.php');

include_once('model/Manager/MemberManager.php');
include_once('model/Manager/PendingManager.php');
include_once('model/Manager/TeamManager.php');
include_once('model/Manager/GuildManager.php');
include_once('model/Manager/CharacterManager.php');
include_once('model/Manager/CrewManager.php');
include_once('model/Entity/Team.php');

class MemberControler extends AbstractControler
{
    private MemberManager $_memberManager;
    private TeamManager $_teamManager;

    public function __construct() {
        parent::__construct("Membres");
        $this->_memberManager = new MemberManager();
        $this->_permissionManager = new PermissionManager();
        $this->_teamManager = new TeamManager();
    }

    function listMembers() {
        try {
            $guilds = (new GuildManager())->getAll();
        } catch (Exception $e){
            $guilds = [];
            $this->addMsg("warning", $e->getMessage());
        }
        try {
            $members = $this->_memberManager->getAll();
        } catch (Exception $e){
            $members = [];
            $this->addMsg("warning", $e->getMessage());
        }
        $v_members = Array();
        foreach ($guilds as $id => $guild) {
            $v_members[$guild->getName()] = Array("guildId" => $guild->getId(),
                          "color" => $guild->getColor(),
                          "members" => Array());
        }
        $v_members["Sans guilde"] = Array("guildId" => 0,
                      "color" => "dark",
                      "members" => Array());
        foreach ($members as $id => $member) {
            if (!isset($member->getGuildInfo()["name"])) {
                $v_members["Sans guilde"]["members"][] = $member;
            } else {
                $v_members[$member->getGuildInfo()["name"]]["members"][] = $member;
            }
        }
        $v_content_header = "Listes des Membres";
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/memberPage/MemberListView.php');
    }

    function addMember() {
        try {
            $v_guilds = (new GuildManager())->getAll();
        } catch (Exception $e){
            $v_guilds = [];
            $this->addMsg("warning", $e->getMessage());
        }
        $v_content_header = "Nouveau membre";
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/memberPage/MemberNewView.php');
    }

    function submitMember($name, $guildId, $dateStart) {
        if (empty($name) or empty($dateStart)) {
            if (empty($dateStart)) { $this->addMsg("warning", "Choisissez la date d'arrivez dans la guilde +24h (pour pouvoir attaquer en raid)");}
            if (empty($name)) { $this->addMsg("warning", "Le pseudo ne doit pas être vide");}
//            return;
        }
        try {
            if ($this->_memberManager->addMember($name, $guildId, $dateStart)) {
                $this->addMsg("success", "Membre ajouté");
            }
        } catch (Exception $e) {
            $this->addMsg("danger", $e->getMessage());
        }
    }

    function editMember($id) {
        try {
            $v_guilds = (new GuildManager())->getAll();
        } catch (Exception $e){
            $v_guilds = [];
            $this->addMsg("danger", $e->getMessage());
        }
        $f_member = $this->_memberManager->getById($id);
        $v_content_header = $f_member->getName();
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/memberPage/MemberEditView.php');
    }

    function saveMember($memberId, $name, $guildId, $dateStart) {
        if (is_null($memberId)) {
            $this->addMsg("danger", "Erreur pendant la mise à jour des informations du joueur");
            return;
        }
        $editParams = Array("name" => $name, "guildId" => $guildId, "dateStart" => $dateStart);
        try {
            return $this->_memberManager->updateMember($memberId, $editParams);
        } catch (Exception $e) {
            $this->addMsg("danger", "Erreur pendant la mise à jour des informations du joueur $e->getMessage()");
        }
    }

    function deleteMember($memberId) {
        if (is_null($memberId)) {
            $this->addMsg("danger", "Erreur pendant la suppression du joueur");
            return;
        }
        $editParams = Array("guildId" => "0", "deleted" => "1");
        try {
            return $this->_memberManager->updateMember($memberId, $editParams);
        } catch (Exception $e) {
            $this->addMsg("danger", "Erreur pendant la suppression du joueur $e->getMessage()");
        }
        $v_msgs = $this->getMsgs();
    }

    function linkMember($isSubmit, $memberId, $pendingId) {
        $pendingManager = new PendingManager();
        if ($isSubmit and !is_null($memberId) and $memberId != 0 and !is_null($pendingId)) {
            try {
                $pending = $pendingManager->getPendingById($pendingId);
                $res = $this->_memberManager->updateMember($memberId,
                    Array("login" => $pending->getLogin(), "passwd" => $pending->getPasswd()));
                if ($res) {
                    $this->addMsg("success", "Membre linké");
                    if ($pendingManager->deletePending($pendingId)) {
                      $_SESSION["nbPending"] -= 1;
                      if ($_SESSION["nbPending"] == 0) { $_SESSION["nbPending"] = NULL;}
                    }
                }
            } catch (Exception $e){
                $this->addMsg("danger", $e->getMessage());
            }
        }
        // Html page
        try {
            $v_members = $this->_memberManager->getAll();
        } catch (Exception $e){
            $this->addMsg("danger", $e->getMessage());
        }
        try {
            $v_pendings = $pendingManager->getAll();
        } catch (Exception $e){
            $this->addMsg("info", $e->getMessage());
        }
        $v_content_header = "Associer un compte à un joueur";
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/memberPage/MemberLinkView.php');
    }

    function listTeam($memberId) {
        $_memberId = $memberId;
        $v_characters = (new CharacterManager())->getAllOrderByGradeElementName();
        $elements = ElementManager::getAllInRawData();
        $teams = $this->_teamManager->getAllByMember($memberId);
        foreach ($teams as $teamNumber => $team) {
            if (!isset($team)) {
                $teams[$teamNumber] = new Team("",
                    $memberId,
                    $teamNumber,
                    0,
                    0,
                    0,
                    0);
            }
        }
        $_team1 = $teams[1];
        $_team2 = $teams[2];
        $_team3 = $teams[3];
        $v_content_header = "Liste des teams";
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/memberPage/MemberTeamView.php');
    }

    function submitTeam($memberId, $teamNumber, $hero1Id, $hero2Id, $hero3Id, $hero4Id) {
        $this->_teamManager->addTeam($memberId, $teamNumber, $hero1Id, $hero2Id, $hero3Id, $hero4Id);
    }

    function saveTeam($id, $hero1Id, $hero2Id, $hero3Id, $hero4Id) {
        $this->_teamManager->updateTeam($id, $hero1Id, $hero2Id, $hero3Id, $hero4Id);
    }

    function saveTeamByMemberAndTeam($id, $hero1Id, $hero2Id, $hero3Id, $hero4Id) {
        $editParams = Array("hero1Id" => $hero1Id,
            "hero2Id" => $hero2Id,
            "hero3Id" => $hero3Id,
            "hero4Id" => $hero4Id);
        $this->_teamManager->updateTeamNyMemberAndTeam($id, $editParams);
    }

    function addOrUpdateTeam($memberId, $teamNumber, $hero1Id, $hero2Id, $hero3Id, $hero4Id)
    {
      $id = $this->_teamManager->getTeamId($memberId, $teamNumber);
        if ($id != 0) {
          $this->saveTeamByMemberAndTeam($id, $hero1Id, $hero2Id, $hero3Id, $hero4Id);
        } else {
            $this->submitTeam($memberId, $teamNumber, $hero1Id, $hero2Id, $hero3Id, $hero4Id);
        }
    }
    
    function addCrew($memberId, $charactId, $level, $evolveld, $nbBreak, bool $hasWeapon, $nbWeaponBreak) {
        if ($_SESSION["id"] != $memberId) {
            $this->addMsg("danger", "Vous ne pouvez pas ajouter les Héros d'une autre membre de l'alliance");
            return;
        }
        if (empty($level) and empty($evolveld) and empty($nbBreak) and !$hasWeapon) {
            return;
        }
        $level = empty($level) ? 0 : $level;
        $evolveld = empty($evolveld) ? 0 : $evolveld;
        $nbBreak = empty($nbBreak) ? 0 : $nbBreak;
        $nbWeaponBreak = $hasWeapon ? (empty($nbWeaponBreak) ? 0 : $nbWeaponBreak) : 0;
        $hasWeapon = $hasWeapon ? 1 : 0;    
        (new CrewManager())->addCrew($memberId, $charactId, $level, $evolveld, $nbBreak, $hasWeapon, $nbWeaponBreak);
    }
    
    function saveCrew($id, $memberId, $charactId, $level, $evolveld, $nbBreak, bool $hasWeapon, $nbWeaponBreak) {
        if ($_SESSION["id"] != $memberId) {
            $this->addMsg("danger", "Vous ne pouvez pas modifier les Héros d'une autre membre de l'alliance");
            return;
        }
        if (is_null($memberId) or is_null($charactId)) {
            $this->addMsg("danger", "Erreur pendant la mise à jour du Héro");
            return;
        }
        // TODO check $id avec $memberId, $charactId
        $level = empty($level) ? 0 : $level;
        $evolveld = empty($evolveld) ? 0 : $evolveld;
        $nbBreak = empty($nbBreak) ? 0 : $nbBreak;
        $nbWeaponBreak = $hasWeapon ? (empty($nbWeaponBreak) ? 0 : $nbWeaponBreak) : 0;
        $hasWeapon = $hasWeapon ? 1 : 0;
        $editParams = Array("level" => $level, "evolvedGrade" => $evolveld, "nbBreak" => $nbBreak,
                            "hasWeapon" => $hasWeapon, "nbWeaponBreak" => $nbWeaponBreak);
        try {
            return (new CrewManager())->updateCrew($id, $editParams);
        } catch (Exception $e) {
            $this->addMsg("danger", "Erreur pendant la mise à jour des informations du Héro $e->getMessage()");
        }
    }

    private function getGradeName($grade) {
      if ($grade == 3) {
        return "Unique";
      } else if ($grade == 2) {
        return "Rare";
      }
      return "Normal";
    }
    
    function printCrew($memberId, $viewPageForced)
    {
        $color1 = "#f98082c4"; //fire
        $color2 = "#0054ff54"; //water
        $color3 = "#82cc89c4"; //ground
        $color4 = "#9f9f9fba"; //dark
        $color5 = "#ffc7127a"; //light
        $color6 = "#ffffffff"; //basic
        
        $frame3 = "#ffd626";
        $frame2 = "#26dbff";

        $elements = ElementManager::getAllInRawData();
        try {
            $f_member = $this->_memberManager->getById($memberId);
        } catch (Exception $e){
            $this->addMsg("danger", $e->getMessage());
        }
        try {
            $_characts = (new CharacterManager)->getAllForMember($memberId);
        } catch (Exception $e){
            $this->addMsg("danger", $e->getMessage());
        }
        $v_heros = [];
        foreach ($_characts as $id => $crew) {
            $frame = 'frame'.$crew->getGrade();
            $color = 'color'.$crew->getElementId();
            $v_heros[$id] = ["isPull" => !is_null($crew->getLevel()),
                "charac" => Array("name" => $crew->getName(),
                          "grade" => ["value" => $crew->getGrade(), "name" => $this->getGradeName($crew->getGrade())],
                          "color" => ["frame" => $$frame, "background" => $$color],
                          "level" => $crew->getLevel(),
                          "stars" => $crew->getEvolvedGrade(), 
                          "nbBreak" => $crew->getNumberBreak(),
                          "hasWeapon" => $crew->hasExclusiveWeapon(),
                          "nbWeaponBreak" => $crew->getNumberWeaponBreak(),
                          "crewId" => $crew->getCrewId(),
                          "element" => $elements[$crew->getElementId()]
                    )
                ];
        }
        $f_memberId = $memberId;
        $v_content_header = "Liste des Héros de ".$f_member->getName();
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        if ($_SESSION["id"] != $f_memberId or !is_null($viewPageForced)) {
          require('view/memberPage/MemberCrewConsultView.php');
        } else {
          require('view/memberPage/MemberCrewUpdateView.php');
        }
    }
}
