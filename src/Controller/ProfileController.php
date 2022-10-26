<?php

namespace App\Controller;

use Exception;
use App\Manager\MemberManager;
use App\Manager\ElementManager;
use App\Manager\CharacterManager;
use App\Manager\CrewManager;
use App\Manager\FightManager;
use App\Manager\RaidManager;
use App\Manager\SettingManager;
use App\Manager\WorldManager;
use App\Validator\PasswordValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ProfileController extends BaseController
{
    private MemberManager $_memberManager;
    private ElementManager $_elementManager;
    private CharacterManager $_characterManager;
    private CrewManager $_crewManager;
    private FightManager $_fightManager;
    private RaidManager $_raidManager;
    private SettingManager $_settingManager;
    private WorldManager $_worldManager;
    
    protected function __init($bag) {
        $this->_characterManager = $bag->get(CharacterManager::class);
        $this->_crewManager = $bag->get(CrewManager::class);
        $this->_elementManager = $bag->get(ElementManager::class);
        $this->_memberManager = $bag->get(MemberManager::class);
        $this->_fightManager = $bag->get(FightManager::class);
        $this->_raidManager = $bag->get(RaidManager::class);
        $this->_settingManager = $bag->get(SettingManager::class);
        $this->_worldManager = $bag->get(WorldManager::class);
    }

    public function me(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        return $this->index($request, $response, $this->session->get('id'));
    }
    
    private function checkIgSettingForm(ServerRequestInterface $request) {
        $form = $request->getParsedBody();
        $update = isset($form["updateIgSettingForm"]);
        if ($update) {
            $id = $form["settingIdForm"];
            $memberId = $form["memberIdForm"];
            $level = $form["levelForm"];
            
            if ($memberId == $this->session->get('id')) {
              $editParams = Array("memberId" => $memberId, "maxLevel" => $level);
              $this->_settingManager->updateEntity($id, $editParams);
            }
        }
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response, $id): ResponseInterface {
        // TEMP
        $color1 = "#f98082c4"; //fire
        $color2 = "#0054ff54"; //water
        $color3 = "#82cc89c4"; //ground
        $color4 = "#9f9f9fba"; //dark
        $color5 = "#ffc7127a"; //light
        $color6 = "#ffffffff"; //basic
        $colors = [1 => $color1, 2 => $color2, 3 => $color3, 4 => $color4, 5 => $color5, 6 => $color6];
        
        $frame3 = "#ffd626";
        $frame2 = "#26dbff";
        $this->checkIgSettingForm($request);

        $elements = $this->_elementManager->getAllInRawData();
        try {
            $_characts = $this->_characterManager->getAllForMember($id);
        } catch (Exception $e){
            $this->session->getFlash()->add("danger", $e->getMessage());
        }
        $v_heros = [];
        foreach ($_characts as $cid => $crew) {
            $frame = 'frame'.$crew->getGrade();
            $color = 'color'.$crew->getElementId();
            $v_heros[$cid] = ["isPull" => !is_null($crew->getLevel()),
                          "charac" => Array("id" => $cid,
                                      "name" => $crew->getName(),
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

        $v_member = $this->_memberManager->getDetailById($id);
        $v_guild = $v_member->getGuildInfo();
        
        $igSettings = $this->_settingManager->getByMemberId($id);
        $maxLevelList = Array();
        $upgradeLevelList = Array();
        foreach ($this->_worldManager->getAll() as $world) {
          $maxLevelList[$world->getNumber()] = [
              'level' => $world->getMaxLevel(),
              'disabled' => $world->isDisabled()];
          if (!$world->isDisabled() and $world->getMaxLevel() > $igSettings->getMaxHeroLevel()) {
            $upgradeLevelList[$world->getNumber()] = [
              'level' => $world->getMaxLevel()];
          }
        }
        $raidInfo = Array();
        foreach ($this->_raidManager->getAllWithNames() as $raid) {
          $raidInfo[$raid->getId()] = ['date' => $raid->getDate(),
                                       $raid->getBoss1Info()['element'] => $raid->getBoss1Info()['id'],
                                       $raid->getBoss2Info()['element'] => $raid->getBoss2Info()['id'],
                                       $raid->getBoss3Info()['element'] => $raid->getBoss3Info()['id'],
                                       $raid->getBoss4Info()['element'] => $raid->getBoss4Info()['id']];
        }
        $fightSummaries = $this->_fightManager->getFightSummaryByPseudoIdGroupByRaidIdBossId($id);
        $stat1Raid = Array();
        $stat2Raid = Array();
        foreach ($elements as $eId => $element) {
          $stat1Raid[$eId] = Array();
          $stat2Raid[$eId] = Array();
          foreach ($raidInfo as $raidId => $info) {
            if (array_key_exists($raidId, $fightSummaries)
                and array_key_exists($info[$eId], $fightSummaries[$raidId])) {
              $stat1Raid[$eId][$raidId] = $fightSummaries[$raidId][$info[$eId]]["count"];
              $stat2Raid[$eId][$raidId] = $fightSummaries[$raidId][$info[$eId]]["sum"];
            } else {
              $stat1Raid[$eId][$raidId] = 0;
              $stat2Raid[$eId][$raidId] = 0;
            }
          }
        }
        foreach ($raidInfo as $raidId => $info) {
          if (array_key_exists($raidId, $fightSummaries)
              and array_key_exists(0, $fightSummaries[$raidId])) {
            $stat1Raid[0][$raidId] = $fightSummaries[$raidId][0]["count"];
            $stat2Raid[0][$raidId] = $fightSummaries[$raidId][0]["sum"];
          } else {
            $stat1Raid[0][$raidId] = 0;
            $stat2Raid[0][$raidId] = 0;
          }
        }

        return $this->view->render($response, 'profile/index.twig', [
            'id' => $id,
            'heros' => $v_heros,
            'isProfile' => $this->session->get('id') === $id,
            'activeTab' => 'heros',
            'member' => [
                'id' => $v_member->getId(),
                'name' => $v_member->getName(),
                'tag' => $v_member->getTag(),
                'perm' => $v_member->getPermInfo()["name"],
                'login' => $v_member->getLogin(),
                'guild' => $v_guild["id"] != 0 ? $v_guild["name"] : '',
                'start' => strftime("%e %B %Y", strtotime($v_member->getDateStart()))
            ],
            'title' => "Profil de ".$v_member->getName(),
            'igSetting' => [
                'maxLevelList' => $maxLevelList,
                'upgradeLevelList' => $upgradeLevelList,
                'id' => $igSettings->getId(),
                'memberId' => $igSettings->getMemberId(),
                'maxLevel' => $igSettings->getMaxHeroLevel(),
            ],
            'stat1' => $stat1Raid,
            'stat2' => $stat2Raid,
            'elements' => $elements,
            'colors' => $colors,
            'raidInfo' => $raidInfo,
        ]);
    }

    public function settings(ServerRequestInterface $request, ResponseInterface $response, $id): ResponseInterface {
        $form = $request->getParsedBody();
        $update = isset($form["updateMemberForm"]);
        if ($update) {
            $id = $form["idForm"];
            $passwd = $form["oldPasswdForm"];
            if ($id == $this->session->get('id') and $this->passwdUpdateGrated($id, $passwd)) {
                $newPasswd = $form["newPasswdForm"];
                $newPasswd = !empty($newPasswd) ? $newPasswd : $passwd;
                $result = (new PasswordValidator())->validate($newPasswd);
                $login = $form["loginForm"];
                if ($result["accept"]) {
                    $this->updateMember($id, $login, $newPasswd);
                } else {
                    $this->addMsg("warning", $result["msg"]);
                }
            } else {
                $this->addMsg("warning", "Mot de passe incorrect");
            }
        }
        return $this->redirect($response, ['profile', ['id' => $id]]);
    }

    //TODO move out of the controller
    private function getGradeName($grade) {
        if ($grade == 3) {
            return "Unique";
        } else if ($grade == 2) {
            return "Rare";
        }
        return "Normal";
    }

    //TODO move out of the controller
    private function passwdUpdateGrated($id, $passwd) {
        $v_member = $this->_memberManager->getDetailById($id);
        return $this->_memberManager->isPasswdCorrect($v_member, $passwd);
    }
  
    //TODO move out of the controller
    private function updateMember($id, $login, $passwd) {
        if (empty($login)) {
          $this->addMsg("warning", "Le login ne doit pas être vide");
          return;
        }
        
        if($this->_memberManager->updateLoginPasswd($id, $login, $passwd)){
            $this->addMsg("success", "Login et/ou mot de passe sauvegardé");
        }
      }
      
    public function upgrade(ServerRequestInterface $request, ResponseInterface $response, $id): ResponseInterface {
        $form = $request->getParsedBody();
        $formReceived = isset($form["automaticLevelUpgradeForm"]);
        if (!$formReceived or $this->session->get('id') != $id)
        {
          return $this->redirect($response, ['profile', ['id' => $id]]);
        }
        
        $level = $form["levelForm"];
        preg_match_all('/characId\-(?<heroesId>\d+)/', implode(" ", array_keys($form)), $matches);
        $heroesId = $matches["heroesId"];
        $crew = $this->_crewManager->getAllByMemberId($id);
        foreach ($heroesId as $heroId) {
          if (array_key_exists($heroId, $crew))  {
            $this->_crewManager->updateEntity($crew[$heroId]["id"], Array("level" => $level + $crew[$heroId]["nbBreak"]));
          }
        }
        $igSetting = $this->_settingManager->getByMemberId($id);
        $editParams = Array("maxLevel" => $level);
        $this->_settingManager->updateEntity($igSetting->getId(), $editParams);
        return $this->redirect($response, ['my-profile']);
    }

}