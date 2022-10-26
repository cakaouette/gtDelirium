<?php

namespace App\Controller;

use Exception;
use App\Manager\BossManager;
use App\Manager\ElementManager;
use App\Manager\CharacterManager;
use App\Manager\RaidManager;
use App\Manager\WorldManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AdminController extends BaseController
{
    private BossManager $_bossManager;
    private CharacterManager $_characterManager;
    private ElementManager $_elementManager;
    private RaidManager $_raidManager;
    private WorldManager $_worldManager;

    protected function __init($bag) {
        $this->_bossManager = $bag->get(BossManager::class);
        $this->_characterManager = $bag->get(CharacterManager::class);
        $this->_elementManager = $bag->get(ElementManager::class);
        $this->_raidManager = $bag->get(RaidManager::class);
        $this->_worldManager = $bag->get(WorldManager::class);
    }

    public function heroes(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        if ($this->session->get("grade") > $this->session->get("Gestion")) return $this->redirect($response, ['403']);

        if ($request->getMethod() === 'POST') {
            $form = $request->getParsedBody();

            $isHeroInsert = isset($form["createHeroForm"]);
            $isHeroUpdate = isset($form["updateHeroForm"]);

            if ($isHeroInsert) {
                $this->submitHero($form["nameForm"], $form["gradeForm"], $form["elementIdForm"]);
            } else if ($isHeroUpdate) {
                $this->updateHero($form["idForm"], $form["nameForm"], $form["gradeForm"], $form["elementIdForm"]);
            }
        }
        try {
            $v_characts = [];
            foreach ($this->_characterManager->getAllOrderByGradeElementName() as $id => $charact) {
                $element = $charact->getElementInfo();
                $v_characts[] = [
                    'id' => $charact->getId(),
                    'name' => $charact->getName(),
                    'grade' => $charact->getGrade(),
                    'element' => [
                        'id' => $element['id'],
                        'name' => $element['name'],
                    ]
                ];
            }
        } catch (Exception $e){
            $v_characts = [];
            $this->addMsg("danger", $e->getMessage());
        }
        return $this->view->render($response, 'admin/heroes.twig', 
                ['characs' => $v_characts,
                 'elements' => $this->_elementManager->getAllInRawData()
                ]);
    }

    public function delhero(ServerRequestInterface $request, ResponseInterface $response, $id): ResponseInterface {
        if ($this->session->get("grade") > $this->session->get("Gestion")) return $this->redirect($response, ['403']);
        if ($this->_characterManager->deleteCharact($id)) {
            $this->addMsg("info", "Héro supprimé");
        }
        return $this->redirect($response, ['admin-heroes']);
    }

    public function todo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        return $this->view->render($response, 'admin/todo.twig');
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
    
    private function submitHero($name, $grade, $elementId) {
        if ($this->checkData($name, $grade, $elementId)) {
            try {
                if ($this->_characterManager->addHero($name, $grade, $elementId)) {
                    $this->addMsg("success", "Héro ajouté");
                }
            } catch (Exception $e) {
                $this->addMsg("danger", $e->getMessage());
            }
        }
        return;
    }
    
    private function updateHero($id, $name, $grade, $elementId) {
        if ($this->checkData($name, $grade, $elementId)) {
            if($this->_characterManager->updateHero($id, $name, $grade, $elementId)){
                $this->addMsg("success", "Héro modifié");
            }
        }
    }
    
    public function worlds(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        if ($this->session->get("grade") > $this->session->get("Gestion")) return $this->redirect($response, ['403']);

        if ($request->getMethod() === 'POST') {
            $form = $request->getParsedBody();

            $isWorldInsert = isset($form["createWorldForm"]);
            $isWorldUpdate = isset($form["updateWorldForm"]);
            
            $id = $form["idForm"];
            $number = $form["numberForm"];
            $maxLevel = $form["maxLevelForm"];
            $disable = isset($form["disableForm"]) ? '1' : '0';

            if ($isWorldInsert) {
              try {
                  if ($this->_worldManager->addEntity($number,
                                                      $maxLevel,
                                                      $disable)) {
                      $this->addMsg("success", "Monde ajouté");
                  }
              } catch (Exception $e) {
                  $this->addMsg("danger", $e->getMessage());
              }
            } else if ($isWorldUpdate) {
              try {
                  if ($this->_worldManager->updateEntity($id,
                                                         $number,
                                                         $maxLevel,
                                                         $disable)) {
                      $this->addMsg("success", "Monde modifié");
                  }
              } catch (Exception $e) {
                  $this->addMsg("danger", $e->getMessage());
              }
            }
        }
        $worlds = Array();
        foreach ($this->_worldManager->getAll() as $world) {
            $worlds[] = [
              'id' => $world->getId(),
              'number' => $world->getNumber(),
              'maxLevel' => $world->getMaxLevel(),
              'disable' => $world->isDisabled(),
            ];
        }
        return $this->view->render($response, 'admin/worlds.twig', 
                ['worlds' => $worlds,
                ]);
    }
    
    public function delWorld(ServerRequestInterface $request, ResponseInterface $response, $id): ResponseInterface {
        if ($this->session->get("grade") > $this->session->get("Gestion")) return $this->redirect($response, ['403']);
        if ($this->_worldManager->deleteEntity($id)) {
            $this->addMsg("info", "Monde supprimé");
        }
        return $this->redirect($response, ['admin-worlds']);
    }

    public function raids(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        if ($request->getMethod() === 'POST') {
            $form = $request->getParsedBody();
            
            $isRaidInsert = isset($form["createRaidForm"]);
            $isRaidUpdate = isset($form["updateRaidForm"]);
            
            $id = $form["idForm"];
            $b1 = $form["boss1IdForm"];
            $e1 = $form["element1IdForm"];
            $b2 = $form["boss2IdForm"];
            $e2 = $form["element2IdForm"];
            $b3 = $form["boss3IdForm"];
            $e3 = $form["element3IdForm"];
            $b4 = $form["boss4IdForm"];
            $e4 = $form["element4IdForm"];
            $date = $form["dateForm"];
            $duration = $form["durationForm"];

            if ($isRaidInsert) {
              try {
                  if ($this->_raidManager->addEntity($date, $duration,
                                                     $b1, $e1, $b2, $e2, $b3, $e3, $b4, $e4)) {
                      $this->addMsg("success", "Raid ajouté");
                  }
              } catch (Exception $e) {
                  $this->addMsg("danger", $e->getMessage());
              }
            } else if ($isRaidUpdate) {
              try {
                  if ($this->_raidManager->updateEntity($id, $date, $duration,
                                                        $b1, $e1, $b2, $e2, $b3, $e3, $b4, $e4)) {
                      $this->addMsg("success", "Raid modifié");
                  }
              } catch (Exception $e) {
                  $this->addMsg("danger", $e->getMessage());
              }
            }
        }
        foreach ($this->_bossManager->getAll() as $boss) {
          $bosses[] = ['id' => $boss->getId(),
                       'name' => $boss->getName(),
                      ];
        }
        foreach ($this->_elementManager->getAll() as $element) {
          $elements[] = ['id' => $element->getId(),
                         'name' => $element->getName(),
                        ];
        }
        
        foreach ($this->_raidManager->getAllWithNames() as $raid) {
          $raids[] = ['id' => $raid->getId(),
                      'date' => $raid->getDate(),
                      'duration' => $raid->getDuration(),
                      'boss1Id' => $raid->getBoss1Info()['id'], 'boss1Nickname' => $raid->getBoss1Info()['shortName'],
                      'element1Id' => $raid->getBoss1Info()['element'], 'element1Name' => $raid->getBoss1Info()['e_name'],
                      'boss2Id' => $raid->getBoss2Info()['id'], 'boss2Nickname' => $raid->getBoss2Info()['shortName'],
                      'element2Id' => $raid->getBoss2Info()['element'], 'element2Name' => $raid->getBoss2Info()['e_name'],
                      'boss3Id' => $raid->getBoss3Info()['id'], 'boss3Nickname' => $raid->getBoss3Info()['shortName'],
                      'element3Id' => $raid->getBoss3Info()['element'], 'element3Name' => $raid->getBoss3Info()['e_name'],
                      'boss4Id' => $raid->getBoss4Info()['id'], 'boss4Nickname' => $raid->getBoss4Info()['shortName'],
                      'element4Id' => $raid->getBoss4Info()['element'], 'element4Name' => $raid->getBoss4Info()['e_name'],
                  ];
        }
        return $this->view->render($response, 'admin/raids.twig', 
                ['raids' => $raids,
                 'bosses' => $bosses,
                 'elements' => $elements,
                ]);
    }
    
    public function delRaid(ServerRequestInterface $request, ResponseInterface $response, $id): ResponseInterface {
        if ($this->session->get("grade") > $this->session->get("Gestion")) return $this->redirect($response, ['403']);
        if ($this->_raidManager->deleteEntity($id)) {
            $this->addMsg("info", "Raid supprimé");
        }
        return $this->redirect($response, ['admin-raids']);
    }

}