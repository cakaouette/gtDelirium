<?php
include_once('controller/AbstractControler.php');

include_once('model/Manager/MemberManager.php');
include_once('model/Manager/CharacterManager.php');


class ProfileControler extends AbstractControler
{
    public function __construct() {
        parent::__construct("Profil");
    }

    public function passwdUpdateGrated($id, $passwd) {
      $v_member = (new MemberManager())->getDetailById($id);
      return $v_member->getPasswd() == md5($passwd.$v_member->getLogin());
    }
    
    public function addErrorPasswd($msg) {
      $this->addMsg("warning", $msg);
    }
    
//    public function submitHero($name, $grade, $elementId) {
//      if ($this->checkData($name, $grade, $elementId)) {
//        try {
//            if ((new CharacterManager())->addHero($name, $grade, $elementId)) {
//                $this->addMsg("success", "Héro ajouté");
//            }
//        } catch (Exception $e) {
//            $this->addMsg("danger", $e->getMessage());
//        }
//      }
//      return;
//    }
//    
    public function updateMember($id, $login, $passwd) {
      if (empty($login)) {
        $this->addMsg("warning", "Le login ne doit pas être vide");
        return;
      }
      
      if((new MemberManager())->updateLoginPasswd($id, $login, $passwd)){
          $this->addMsg("success", "Login et/ou mot de passe sauvegardé");
      }
    }
//    
//    public function deleteHero($id)
//    {
//        if ((new CharacterManager())->deleteCharact($id)) {
//            $this->addMsg("info", "Héro supprimé");
//        }
//        header('Location: ?page=admin&subpage=heros');
//    }

    private function getGradeName($grade) {
      if ($grade == 3) {
        return "Unique";
      } else if ($grade == 2) {
        return "Rare";
      }
      return "Normal";
    }
    
    public function printPage($memberId, $IsProfile, $activeTab = "heros")
    {
      if (empty($memberId)) {
        return;
      }
        // TEMP
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

      
      
        $v_isProfile = $IsProfile;
        $v_activeTab = $activeTab;
        $v_member = (new MemberManager())->getDetailById($memberId);
              
        $v_content_header = "Profil de ".$v_member->getName();
        $v_title = $this->getTitle()." ".$v_member->getName();
        $v_msgs = $this->getMsgs();
        require('view/profilePage/ProfileView.php');
    }    
}
