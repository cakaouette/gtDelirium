<?php

namespace App\Controller;

use Exception;
use App\Manager\ElementManager;
use App\Manager\CharacterManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AdminController extends BaseController
{
    private CharacterManager $_characterManager;
    private ElementManager $_elementManager;

    protected function __init($bag) {
        $this->_characterManager = $bag->get(CharacterManager::class);
        $this->_elementManager = $bag->get(ElementManager::class);
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
        return $this->view->render($response, 'admin/heroes.twig', ['characs' => $v_characts, 'elements' => $this->_elementManager->getAllInRawData()]);
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
}