<?php
include_once('controller/AbstractControler.php');

include_once('model/Manager/CharacterManager.php');


class AdminControler extends AbstractControler
{
    public function __construct() {
        parent::__construct("Administration");
    }

    private function checkData($name, $grade, $elementId) {
      $result = true;
      if (empty($name)) {
        $this->addMsg("warning",
              "Il manque le nom pour le héro");
        $result = false;
      }
      if (is_null($grade)) {
        $this->addMsg("warning",
              "Il manque le grade pour le héro");
        $result = false;
      }
      if ($elementId == 0) {
        $this->addMsg("warning",
              "Il manque l'élément du héro");
        $result = false;
      }
      return $result;
    }
    
    public function submitHero($name, $grade, $elementId) {
      if ($this->checkData($name, $grade, $elementId)) {
        try {
            if ((new CharacterManager())->addHero($name, $grade, $elementId)) {
                $this->addMsg("success", "Héro ajouté");
            }
        } catch (Exception $e) {
            $this->addMsg("danger", $e->getMessage());
        }
      }
      return;
    }
    
    public function updateHero($id, $name, $grade, $elementId) {
      if ($this->checkData($name, $grade, $elementId)) {
        if((new CharacterManager())->updateHero($id, $name, $grade, $elementId)){
                $this->addMsg("success", "Héro modifié");
        }
      }
    }
    
    public function deleteHero($id)
    {
        if ((new CharacterManager())->deleteCharact($id)) {
            $this->addMsg("info", "Héro supprimé");
        }
        header('Location: ?page=admin&subpage=heros');
    }
    
    public function printHeroWeapon()
    {
        try {
            $v_characts = (new CharacterManager)->getAllOrderByGradeElementName();
        } catch (Exception $e){
            $v_characts = [];
            $this->addMsg("danger", $e->getMessage());
        }
        $v_elements = ElementManager::getAllInRawData();

        $v_content_header = "Liste des Héros et des armes";
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/adminPage/AdminHeroView.php');
    }
}
