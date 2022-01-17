<?php

namespace App\Controller;

use Exception;
use Slim\Views\Twig;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;
use Psr\Http\Message\ServerRequestInterface;

//TODO use namespace and use instead of require once migration is over
require_once('model/Manager/ElementManager.php');
use ElementManager;
require_once('model/Manager/CharacterManager.php');
use CharacterManager;
require_once('model/Manager/MemberManager.php');
use MemberManager;

final class ProfileController
{
    private Twig $view;
    private SessionInterface $session;
    private RouteParserInterface $router;

    /**
     * The constructor.
     *
     * @param Twig $twig The twig template engine
     */
    public function __construct(Twig $twig, SessionInterface $session, RouteParserInterface $router) {
        $this->view = $twig;
        $this->session = $session;
        $this->router = $router;
    }

    public function me(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        return $this->index($request, $response, $this->session->get('id'));
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response, $id): ResponseInterface {
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
            $_characts = (new CharacterManager)->getAllForMember($id);
        } catch (Exception $e){
            $this->session->getFlash()->add("danger", $e->getMessage());
        }
        $v_heros = [];
        foreach ($_characts as $cid => $crew) {
            $frame = 'frame'.$crew->getGrade();
            $color = 'color'.$crew->getElementId();
            $v_heros[$cid] = ["isPull" => !is_null($crew->getLevel()),
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

      
      
        $v_member = (new MemberManager())->getDetailById($id);
        $v_guild = $v_member->getGuildInfo();
              
        return $this->view->render($response, 'profile/index.twig', [
            'heros' => $v_heros,
            'isProfile' => true,
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
            'title' => "Profil de ".$v_member->getName()
        ]);
    }

    public function settings(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
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
}