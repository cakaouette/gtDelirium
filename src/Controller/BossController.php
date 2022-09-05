<?php

namespace App\Controller;

use Exception;
use App\Manager\BossManager;
use App\Manager\WeaponManager;
use App\Manager\AilmentManager;
use App\Manager\AilmentEnduranceManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class BossController extends BaseController
{
    private BossManager $_bossManager;
    private WeaponManager $_weaponManager;
    private AilmentManager $_ailmentManager;
    private AilmentEnduranceManager $_ailmentEnduranceManager;
    
    protected function __init($bag) {
        $this->_bossManager = $bag->get(BossManager::class);
        $this->_weaponManager = $bag->get(WeaponManager::class);
        $this->_ailmentManager = $bag->get(AilmentManager::class);
        $this->_ailmentEnduranceManager = $bag->get(AilmentEnduranceManager::class);
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        try {
            $v_bosses = $this->_bossManager->getAll();
        } catch (Exception $e) {
            $v_bosses = Array();
        }
        $bosses = [];
        foreach ($v_bosses as $bossId => $boss) { 
            $bosses[$bossId] = $boss->getName();
        }

        return $this->view->render($response, 'boss/index.twig', ['bosses' => $bosses]);
    }

    public function info(ServerRequestInterface $request, ResponseInterface $response, $id): ResponseInterface {
        try {
            $v_boss = $this->_bossManager->getBossById($id);
            $data = [
                'title' => "Description de ".$v_boss->getName(),
                'id' => $v_boss->getId(),
                'name' => $v_boss->getName(),
                'ailments' => []
            ];
            $_ailments = $this->_ailmentManager->getAll();
            $v_ailments = Array();
            foreach ($_ailments as $aId => $ailment) {
                $weapons = [];
                foreach ($this->_weaponManager->getByAilmentBoss($aId, $id) as $wId => $weapon) {
                    $charac = $weapon->getCharacInfo();
                    $weapons[$wId] = [
                        'name' => $weapon->getName(),
                        'charac' => [
                            'id' => $charac["id"],
                            'name' => $charac["name"]
                        ],
                        'bossId' => $v_boss->getId()
                    ];
                    if (!is_null($weapon->getRate())) {
                        $weapons[$wId]['AeId'] = $weapon->getAeId();
                        $weapons[$wId]['bossId'] = $weapon->getBossInfo()["id"];
                        $weapons[$wId]['rate'] = $weapon->getRate();
                    }
                }
                $data['ailments'][$aId] = [
                    'name' => $ailment->getName(),
                    'weapons' => $weapons
                ];
            }
        } catch (Exception $e) {
            $this->addMsg("warning", $e->getMessage());
            $v_boss = NULL;
            $v_ailments = Array();
            $data = [];
        }

        return $this->view->render($response, 'boss/info.twig', $data);
    }

    public function ailment(ServerRequestInterface $request, ResponseInterface $response, $id) {
        $form = $request->getParsedBody();
        $ailmentEnduranceIdForm = $form['ailmentEnduranceIdForm'];
        $passwd = $form['passwdForm'];

        $isInsert = false;
        $isUpdate = false;
        if (isset($_POST["updateForm"])) {
            $isInsert = empty($ailmentEnduranceIdForm);
            $isUpdate = !empty($ailmentEnduranceIdForm);
        }
        
        if ($isInsert) {
            $this->addAe(
                $weaponId = $form["weapondIdForm"],
                $bossId = $form["bossIdForm"],
                $rate = $form["rateForm"],
            );
        } elseif ($isUpdate) {
            $this->updateAe(
                $id = $form["ailmentEnduranceIdForm"],
                $weaponId = $form["weapondIdForm"],
                $bossId = $form["bossIdForm"],
                $rate = $form["rateForm"],
            );
        }
        return $this->redirect($response, ['boss', ['id' => $bossId]]);
    }

    private function addAe($weaponId, $bossId, $rate) {
        try {
            if ($this->_ailmentEnduranceManager->add($weaponId, $bossId, $rate)) {
                $this->addMsg("success", "info ajoutée");
            }
        } catch (Exception $e) {
            $this->addMsg("danger", $e->getMessage());
        }
    }

    private function updateAe($id, $weaponId, $bossId, $rate) {
        if (empty($id)) {
            $this->addMsg("danger", "Erreur pendant la mise à jour des informations");
            return;
        }
        $editParams = Array("weaponId" => $weaponId, "bossId" => $bossId, "rate" => $rate);
        try {
            return $this->_ailmentEnduranceManager->updateAe($id, $editParams);
        } catch (Exception $e) {
            $this->addMsg("danger", "Erreur pendant la mise à jour des informations");
        }
    }
}
