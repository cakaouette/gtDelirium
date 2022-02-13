<?php

namespace App\Controller;

use Exception;
use App\Manager\TeamManager;
use App\Manager\CrewManager;
use App\Manager\GuildManager;
use App\Manager\MemberManager;
use App\Manager\ElementManager;
use App\Manager\PendingManager;
use App\Manager\CharacterManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class MemberController extends BaseController
{
    private CharacterManager $_characterManager;
    private ElementManager $_elementManager;
    private PendingManager $_pendingManager;
    private MemberManager $_memberManager;
    private GuildManager $_guildManager;
    private TeamManager $_teamManager;
    private CrewManager $_crewManager;
    
    protected function __init($bag) {
        $this->_characterManager = $bag->get(CharacterManager::class);
        $this->_elementManager = $bag->get(ElementManager::class);
        $this->_pendingManager = $bag->get(PendingManager::class);
        $this->_memberManager = $bag->get(MemberManager::class);
        $this->_guildManager = $bag->get(GuildManager::class);
        $this->_teamManager = $bag->get(TeamManager::class);
        $this->_crewManager = $bag->get(CrewManager::class);
    }

    public function new(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        if ($this->session->get("grade") > $this->session->get("Officier")) return $this->redirect($response, ['403']);

        if ($request->getMethod() === 'POST') {
          $form = $request->getParsedBody();
          $isInsert = isset($form["addMemberForm"]);
          if ($isInsert) {
              $this->submitMember($form['nameForm'], $form['guildForm'], $form['dateStartForm']);
          }
        }
        try {
            $v_guilds = [];
            foreach ($this->_guildManager->getAll() as $guild) {
              $v_guilds[] = ['id' => $guild->getId(), 'name' => $guild->getName()];
            }
        } catch (Exception $e){
            $v_guilds = [];
            $this->addMsg("warning", $e->getMessage());
        }

        return $this->view->render($response, 'member/new.twig', ['guilds' => $v_guilds]);
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, string $îd): ResponseInterface {
        if ($this->session->get("grade") > $this->session->get("Officier")) return $this->redirect($response, ['403']);
        $this->deleteMember($id);
        return $this->redirect($response, ['members-alliance']);
    }

    public function edit(ServerRequestInterface $request, ResponseInterface $response, string $id): ResponseInterface {
        if ($this->session->get("grade") > $this->session->get("Officier")) return $this->redirect($response, ['403']);

        if ($request->getMethod() === 'POST') {
            $form = $request->getParsedBody();
            $isUpdate = isset($form["editMemberForm"]);
            if ($isUpdate) {
                $this->saveMember($form['idForm'], $form['nameForm'], $form['guildForm'], $form['dateStartForm']);
            }
        }
        try {
            $v_guilds = [];
            foreach ($this->__guildManager->getAll() as $guild) {
              $v_guilds[] = ['id' => $guild->getId(), 'name' => $guild->getName()];
            }
        } catch (Exception $e){
            $v_guilds = [];
            $this->addMsg("danger", $e->getMessage());
        }
        $f_member = $this->_memberManager->getById($id);
    
        return $this->view->render($response, 'member/edit.twig', [
            'title' => $f_member->getName(),
            'id' => $f_member->getId(),
            'name' => $f_member->getName(),
            'startDate' => $f_member->getDateStart(),
            'guildId' => $f_member->getGuildInfo()["id"],
            'guilds' => $v_guilds,
        ]);
    }

    public function team(ServerRequestInterface $request, ResponseInterface $response, string $id): ResponseInterface {
        if ($this->session->get("grade") > $this->session->get("Officier") && $this->session->get('id') != $id) return $this->redirect($response, ['403']);

        if ($request->getMethod() === 'POST') {
            $form = $request->getParsedBody();
            $isInsert = isset($form["team1AddForm"]);
            $isInsert += isset($form["team2AddForm"]);
            $isInsert += isset($form["team3AddForm"]);
            $isUpdate = isset($form["team1UpdateForm"]);
            $isUpdate += isset($form["team2UpdateForm"]);
            $isUpdate += isset($form["team3UpdateForm"]);

            if ($isInsert) {
                $this->submitTeam(
                    $form["memberForm"],
                    $form["teamForm"],
                    $form["hero1Form"],
                    $form["hero2Form"],
                    $form["hero3Form"],
                    $form["hero4Form"]
                );
            } elseif ($isUpdate) {
                $this->saveTeam(
                    $form["idForm"],
                    $form["hero1Form"],
                    $form["hero2Form"],
                    $form["hero3Form"],
                    $form["hero4Form"]
                );
            }
        }
        $v_characters = $this->_characterManager->getAllOrderByGradeElementName();
        $characters = [];
        foreach ($v_characters as $character) {
            $g = $character->getGrade();
            if (!isset($characters[$g])) $characters[$g] = ['name' => $g, 'elements' => []];
            $e = $character->getElementInfo();
            if (!isset($characters[$g]['elements'][$e['id']])) $characters[$g]['elements'][$e['id']] = ['name' => $e['name'], 'characters' => []];
            $characters[$g]['elements'][$e['id']]['characters'][$character->getId()] = $character->getName();
        }
        $v_teams = $this->_teamManager->getAllByMember($id);
        $teams = [];
        for ($i = 1; $i <= 3; $i++) {
            if (isset($v_teams[$i])) {
                $team = $v_teams[$i];
                $teams[$i] = [
                    'id' => $team->id,
                    'members' => [
                        1 => $team->getHero1Id(),
                        2 => $team->getHero2Id(),
                        3 => $team->getHero3Id(),
                        4 => $team->getHero4Id(),
                    ]
                ];
            }
            else {
                $teams[$i] = ['id' => 0, 'members' => [1 => 0, 2 => 0, 3 => 0, 4 => 0]];
            }
        }
        return $this->view->render($response, 'member/team.twig', ['id' => $id, 'teams' => $teams, 'characters' => $characters]);
    }

    public function pending(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        if ($this->session->get("grade") > $this->session->get("Officier")) return $this->redirect($response, ['403']);

        if ($request->getMethod() === 'POST') {
            $form = $request->getParsedBody();
            $isSubmit = isset($form["linkAccount"]);
            $memberId = $form["memberForm"];
            $pendingId = $form["pendingForm"];
            if ($isSubmit and !is_null($memberId) and $memberId != 0 and !is_null($pendingId)) {
                try {
                    $pending = $this->_pendingManager->getPendingById($pendingId);
                    $res = $this->_memberManager->updateMember($memberId,
                        Array("login" => $pending->getLogin(), "passwd" => $pending->getPasswd()));
                    if ($res) {
                        $this->addMsg("success", "Membre linké");
                        if ($this->_pendingManager->deletePending($pendingId)) {
                            $pendingCount = $this->session->get('nbPending') - 1;
                            if ($pendingCount <= 0) $this->session->set('nbPending', NULL);
                            else $this->session->set('nbPending', $pendingCount);
                        }
                    }
                } catch (Exception $e){
                    $this->addMsg("danger", $e->getMessage());
                }
            }
        }

        try {
            $v_members = [];
            foreach ($this->_memberManager->getAll() as $member) {
                $v_members[$member->getId()] = $member->getName();
            }
        } catch (Exception $e){
            $this->addMsg("danger", $e->getMessage());
        }
        try {
            $v_pendings = [];
            foreach ($this->_pendingManager->getAll() as $pending) {
                $v_pendings[$pending->getId()] = $pending->getPseudo();
            }
        } catch (Exception $e){
            $this->addMsg("info", $e->getMessage());
        }
        
        return $this->view->render($response, 'member/pending.twig', ['members' => $v_members, 'pendings' => $v_pendings]);
    }

    public function alliance(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        try {
            $guilds = $this->_guildManager->getAll();
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
            $m = ['name' => $member->getName(), 'startDate' => $member->getDateStart()];
            if (!isset($member->getGuildInfo()["name"])) {
                $v_members["Sans guilde"]["members"][$id] = $m;
            } else {
                $v_members[$member->getGuildInfo()["name"]]["members"][$id] = $m;
            }
        }

        return $this->view->render($response, 'member/alliance.twig', ['members' => $v_members]);
    }

    public function crew(ServerRequestInterface $request, ResponseInterface $response, string $id): ResponseInterface {
        return $this->crewBase($request, $response, $id, 'view');
    }

    public function crewedit(ServerRequestInterface $request, ResponseInterface $response, string $id): ResponseInterface {
        return $this->crewBase($request, $response, $id, 'edit');
    }

    private function crewBase(ServerRequestInterface $request, ResponseInterface $response, string $id, string $template): ResponseInterface {
        if ($this->session->get("id") != $id) return $this->redirect($response, ['profile', ['id' => $id]]);

        if ($request->getMethod() === 'POST') {
            $form = $request->getParsedBody();
            $isPull = isset($form["pullHeroOrWeaponForm"]);
            $isUpdate = isset($form["updateForm"]);
            $isMlb = isset($form["isMlbForm"]);
            $f_id = $form["crewIdForm"];
            $f_memberId = $form["memberIdForm"];
            $f_charactId = $form["charactIdForm"];
            $f_level = $form["levelForm"];
            $f_evolved = $form["evolutionForm"];
            $f_nbBreak = $form["breakForm"];
            $f_hasWeapon = isset($form["weaponForm"]);
            $f_nbWeaponBreak = $form["weaponBreakForm"];
            if ($isMlb) {
                $f_level = 83;
                $f_evolved = 5;
                $f_nbBreak = 5;
            }
            if ($isPull) {
                if ($this->session->get("grade") > $this->session->get("Joueur")) return $this->redirect($response, ['403']);
                $this->addCrew($f_memberId, $f_charactId, $f_level, $f_evolved, $f_nbBreak, $f_hasWeapon, $f_nbWeaponBreak);
            } else if ($isUpdate) {
                if ($this->session->get("grade") > $this->session->get("Joueur")) return $this->redirect($response, ['403']);
                $this->saveCrew($f_id, $f_memberId, $f_charactId, $f_level, $f_evolved, $f_nbBreak, $f_hasWeapon, $f_nbWeaponBreak);
            }
        }
        $color1 = "#f98082c4"; //fire
        $color2 = "#0054ff54"; //water
        $color3 = "#82cc89c4"; //ground
        $color4 = "#9f9f9fba"; //dark
        $color5 = "#ffc7127a"; //light
        $color6 = "#ffffffff"; //basic
        
        $frame3 = "#ffd626";
        $frame2 = "#26dbff";

        $elements = $this->_elementManager->getAllInRawData();
        try {
            $f_member = $this->_memberManager->getById($id);
        } catch (Exception $e){
            $this->addMsg("danger", $e->getMessage());
        }
        try {
            $_characts = $this->_characterManager->getAllForMember($id);
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

        return $this->view->render($response, 'member/crew-'.$template.'.twig', [
            'title' => "Liste des Héros de ".$f_member->getName(),
            'id' => $f_member->getId(),
            'name' => $f_member->getName(),
            'heroes' => $v_heros,
        ]);
    }

    private function submitMember($name, $guildId, $dateStart) {
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

    private function saveMember($memberId, $name, $guildId, $dateStart) {
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

    private function deleteMember($memberId) {
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
    }

    private function submitTeam($memberId, $teamNumber, $hero1Id, $hero2Id, $hero3Id, $hero4Id) {
        $this->_teamManager->addTeam($memberId, $teamNumber, $hero1Id, $hero2Id, $hero3Id, $hero4Id);
    }

    private function saveTeam($id, $hero1Id, $hero2Id, $hero3Id, $hero4Id) {
        $this->_teamManager->updateTeam($id, $hero1Id, $hero2Id, $hero3Id, $hero4Id);
    }

    private function addCrew($memberId, $charactId, $level, $evolveld, $nbBreak, bool $hasWeapon, $nbWeaponBreak) {
        if ($this->session->get("id") != $memberId) {
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
        $this->_crewManager->addCrew($memberId, $charactId, $level, $evolveld, $nbBreak, $hasWeapon, $nbWeaponBreak);
    }
    
    private function saveCrew($id, $memberId, $charactId, $level, $evolveld, $nbBreak, bool $hasWeapon, $nbWeaponBreak) {
        if ($this->session->get("id") != $memberId) {
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
            return $this->_crewManager->updateCrew($id, $editParams);
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
}