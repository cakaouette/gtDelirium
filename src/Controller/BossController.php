<?php

namespace App\Controller;

use Exception;
use App\Manager\BossManager;
use App\Manager\BossInfoManager;
use App\Manager\BossStrategyManager;
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
    private BossInfoManager $_bossInfoManager;
    private BossStrategyManager $_bossStrategyManager;

    protected function __init($bag) {
        $this->_bossManager = $bag->get(BossManager::class);
        $this->_weaponManager = $bag->get(WeaponManager::class);
        $this->_ailmentManager = $bag->get(AilmentManager::class);
        $this->_ailmentEnduranceManager = $bag->get(AilmentEnduranceManager::class);
        $this->_bossInfoManager = $bag->get(BossInfoManager::class);
        $this->_bossStrategyManager = $bag->get(BossStrategyManager::class);
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
    
    private function getRateCategory($rates) {
      if ($rates >= 50) {
        return 'green';
      } elseif ($rates > 33.33) {
        return 'yellow';
      } elseif ($rates >= 25) {
        return 'pink';
      }
      return 'dark';
    }

    private function getFightsCountByCategory($category) {
      if ($category == 'green') {
        return 2;
      } elseif ($category == 'yellow') {
        return 3;
      } elseif ($category == 'pink') {
        return 4;
      } elseif ($category == 'dark') {
        return 5;
      }
      return '-';
    }

    public function info(ServerRequestInterface $request, ResponseInterface $response, $id): ResponseInterface {
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
            $rateCategories = [];
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
                    $rateCategories[] = $this->getRateCategory($weapon->getRate());
                    $weapons[$wId]['AeId'] = $weapon->getAeId();
                    $weapons[$wId]['bossId'] = $weapon->getBossInfo()["id"];
                    $weapons[$wId]['rate'] = $weapon->getRate();
                }
            }
            $ratesNbByCategory = array_count_values($rateCategories);
            arsort($ratesNbByCategory);
            $averge = count($ratesNbByCategory) >= 1 ? array_key_first($ratesNbByCategory) : 'default';
            $data['ailments'][$aId] = [
                'name' => $ailment->getName(),
                'weapons' => $weapons,
                'average' => $averge,
                'fightsCount' => $this->getFightsCountByCategory($averge),
            ];
        }
        
        $infos = Array();
        $imgBossPath = $this->configDirPath->get('imageRelative').$this->configDirPath->get('bossInfo');
        foreach ($this->_bossInfoManager->getAllByBoss($id) as $infoId => $info) {
          if ($info->getType() == "image") {
            $infos[] = ["source" => $imgBossPath.$id."_".$infoId.".".$info->getExtension(),
                        "imgSrc" => $imgBossPath.$id."_".$infoId.".".$info->getExtension(),
                        "originalName" => $info->getSource()];
          } else if ($info->getType() == "video") {
            $infos[] = ["source" => $info->getSource(),
                        "imgSrc" => $imgBossPath."video.png",
                        "originalName" => ""];
          }
        }
        $data['infos'] = $infos;
        $data['text'] = $this->_bossStrategyManager->getBossStrategyById($id)->getStrategy();

      return $this->render($response,
                           'boss/info.twig',
                           $data);
    }

    public function addInfo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
      $form = $request->getParsedBody();
      $directory = $this->configDirPath->get('imageRoot').$this->configDirPath->get('bossInfo');
      $bossId = $form['bossIdForm'];
      if (is_null($bossId)) {
        return $this->redirect($response, ['bosses']);
      }

      $uploadedFiles = $request->getUploadedFiles();
      $infos = $this->_bossInfoManager->getAllByBoss($bossId);
      
      $infoId = empty($infos) ? 0 : max(array_keys($infos))+1;
      foreach ($uploadedFiles["files"] as $uploadedFile) {
        if ($uploadedFile->getError() === UPLOAD_ERR_OK
            and str_contains($uploadedFile->getClientMediaType(), 'image')) {
          $type = 'image';
          $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
          $source = pathinfo($uploadedFile->getClientFilename(), PATHINFO_BASENAME);
          if ($this->_bossInfoManager->addEntity($bossId, $infoId, $type, $extension, $source)) {
            $uploadedFile->moveTo($directory.$bossId."_".$infoId.".".$extension);
            $infoId+=1;
          } else {

          }
        }
      }
      if (!empty($form["videoLinkForm"])) {
        $type = 'video';
        $extension = "";
        $source = $form["videoLinkForm"];
        $this->_bossInfoManager->addEntity($bossId, $infoId, $type, $extension, $source);
      }
      return $this->redirect($response, ['boss', ['id' => $bossId]]);
    }
    
    public function addStrategy(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
      $form = $request->getParsedBody();
      $bossId = $form['bossIdForm'];
      if (is_null($bossId)) {
        return $this->redirect($response, ['bosses']);
      }
      $strategy = $form['strategyForm'];
      $strat = $this->_bossStrategyManager->getBossStrategyById($bossId);
      if ($strat->getId() != 0) {
        $this->_bossStrategyManager->updateEntity($strat->getId(), $strategy);
      } else {
        $this->_bossStrategyManager->addEntity($bossId, $strategy);
      }
      
      return $this->redirect($response, ['boss', ['id' => $bossId]]);
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
