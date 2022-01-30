<?php

namespace App\Controller;

use Exception;
use App\Validator\PasswordValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

//TODO use namespace and use instead of require once migration is over
require_once('model/Manager/GuildManager.php');
use GuildManager;
require_once('model/Manager/MemberManager.php');
use MemberManager;
require_once('model/Manager/RankManager.php');
use RankManager;
require_once('model/Manager/RankGoalManager.php');
use RankGoalManager;
require_once('model/Manager/RaidManager.php');
use RaidManager;

final class AllianceController extends BaseController
{
    private GuildManager $_guildManager;

    protected function __init() {
        $this->_guildManager = new GuildManager();
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $guilds = $this->_guildManager->getAll();
        $v_info = Array();
        try {
            $counts = (new MemberManager())->countMemberByGuildId();
            $ranks = (new RankManager())->getAll();
            $objectives = (new RankGoalManager())->getAll();
            $raids = (new RaidManager())->getDates();
        } catch (Exception $e) {
            $guilds = [];
            $this->addMsg("warning", $e->getMessage());
            $this->addMsg("warning", "erreur interne");
        }
        foreach ($guilds as $guildId => $guild) {
            $v_info[$guildId] = Array(
                "guild" => ["name" => $guild->getName(), "color" => $guild->getColor()],
                "memberNumber" => $counts[$guildId],
                "ranks" => $ranks[$guildId],
                "lastRank" => end($ranks[$guildId])["rank"]
            );
        }
        foreach ($objectives as $guildId => $objective) {
            $v_info[$guildId]["objectives"] = $objective;
        }
        $v_raids = [];
        foreach ($raids as $raid) {
            $v_raids[$raid->getId()] = strtotime($raid->getDate());
        }
        ksort($v_raids);

        $rankTremens = Array(); $objectivTremens = Array(); $toSetTremens = false; $lastObjectivTremens = NULL;
        $rankNocturnum = Array(); $objectivNocturnum = Array(); $toSetNocturnum = false; $lastObjectivNocturnum = NULL;
        $rankChill = Array();
        
        foreach ($v_info[1]["ranks"] as $raidId => $info) {
          $rankTremens[] = $info["timestamp"].", ".$info["rank"];
        }
        $dataTremens = implode("],[", $rankTremens);
        
        foreach ($v_raids as $raidId => $timeRaid) {
          if (array_key_exists($raidId, $v_info[1]["objectives"])) {
            $objectiv = $v_info[1]["objectives"][$raidId]["rank"];
            if ($toSetTremens) {
              $objectivTremens[] = $timeRaid.", ".$lastObjectivTremens;
            }
            $objectivTremens[] = $timeRaid.", ".$objectiv;
            $lastObjectivTremens = $objectiv;
            $toSetTremens = false;
          } else {
            $toSetTremens = true;
          }
          
          if (array_key_exists($raidId, $v_info[2]["objectives"])) {
            $objectiv = $v_info[2]["objectives"][$raidId]["rank"];
            if ($toSetNocturnum) {
              $objectivNocturnum[] = $timeRaid.", ".$lastObjectivNocturnum;
            }
            $objectivNocturnum[] = $timeRaid.", ".$objectiv;
            $lastObjectivNocturnum = $objectiv;
            $toSetNocturnum = false;
          } else {
            $toSetNocturnum = true;
          }
        }
        if ($toSetTremens) {
          $objectivTremens[] = end($v_raids).", ".$lastObjectivTremens;
        }
        $dataObjectivTremens = implode("],[", $objectivTremens);
        if ($toSetNocturnum) {
          $objectivNocturnum[] = end($v_raids).", ".$lastObjectivNocturnum;
        }
        $dataObjectivNocturnum = implode("],[", $objectivNocturnum);
        
        
        foreach ($v_info[2]["ranks"] as $raidId => $info) {
          $rankNocturnum[] = $info["timestamp"].", ".$info["rank"];
        }
        $dataNocturnum = implode("],[", $rankNocturnum);
        
        foreach ($v_info[3]["ranks"] as $raidId => $info) {
          $rankChill[] = $info["timestamp"].", ".$info["rank"];
        }
        $dataChill = implode("],[", $rankChill);

        return $this->view->render($response, 'alliance/index.twig', [
            'infos' => $v_info,
            //TODO create array instead of decoding a string and then encoding it again
            'dataTremens' => json_decode('[['.$dataTremens.']]'),
            'dataNocturnum' => json_decode('[['.$dataNocturnum.']]'),
            'dataChill' => json_decode('[['.$dataChill.']]'),
            'dataObjectivTremens' => json_decode('[['.$dataObjectivTremens.']]'),
            'dataObjectivNocturnum' => json_decode('[['.$dataObjectivNocturnum.']]'),
        ]);
    }

    public function new(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        if ($request->getMethod() === 'POST') {
            $form = $request->getParsedBody();
            try {
                if ($this->_guildManager->add($form["nameForm"], $form["colorForm"])) {
                    $this->addMsg("success", "Guilde ajoutée");
                    return $response->withStatus(302)->withHeader('Location', $this->router->urlFor('alliance'));
                }
            } catch (Exception $e) {
                $this->addMsg("danger", $e->getMessage());
            }
        }
        return $this->view->render($response, 'alliance/guild.twig', ['id' => 0]);
    }

    public function guild(ServerRequestInterface $request, ResponseInterface $response, $id): ResponseInterface {
        //TODO
    }
}