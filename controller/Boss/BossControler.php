<?php
include_once('controller/AbstractControler.php');

include_once('model/Manager/BossManager.php');
include_once('model/Entity/Boss.php');

include_once('model/Manager/AilmentManager.php');
include_once('model/Manager/AilmentEnduranceManager.php');
include_once('model/Manager/WeaponManager.php');

class BossControler extends AbstractControler
{
    private BossManager $_bossManager;
    
    public function __construct() {
        parent::__construct("Bosses");
        $this->_bossManager = new BossManager();
    }

    public function listBosses() {
        try {
            $v_bosses = $this->_bossManager->getAll();
        } catch (Exception $e) {
            $v_bosses = Array();
        }

        $v_content_header = "Liste des Boss";
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/bossPage/BossListView.php');
    }

    public function info($bossId) {
        try {
            $v_boss = $this->_bossManager->getBossById($bossId);
            $_ailments = (new AilmentManager)->getAll();
            $v_ailments = Array();
            foreach ($_ailments as $id => $ailment) {
              $v_ailments[$id] = ["ailment" => $ailment,
                  "weapons" => (new WeaponManager())->getByAilmentBoss($id, $bossId)
                  ];
            }
        } catch (Exception $e) {
            $this->addMsg("warning", $e->getMessage());
            $v_boss = NULL;
            $v_ailments = Array();
        }

        $v_content_header = "Description de ".(!is_null($v_boss) ? $v_boss->getName() : "");
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/bossPage/BossInfoView.php');
    }
    
    public function addAe($weaponId, $bossId, $rate) {
        try {
            if ((new AilmentEnduranceManager())->add($weaponId, $bossId, $rate)) {
                $this->addMsg("success", "info ajoutée");
            }
        } catch (Exception $e) {
            $this->addMsg("danger", $e->getMessage());
        }
    }

    function updateAe($id, $weaponId, $bossId, $rate) {
        if (empty($id)) {
            $this->addMsg("danger", "Erreur pendant la mise à jour des informations");
            return;
        }
        $editParams = Array("weaponId" => $weaponId, "bossId" => $bossId, "rate" => $rate);
        try {
            return (new AilmentEnduranceManager())->updateAe($id, $editParams);
        } catch (Exception $e) {
            $this->addMsg("danger", "Erreur pendant la mise à jour des informations");
        }
    }

}
