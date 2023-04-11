<?php

namespace App\Controller;

use Exception;
use App\Manager\RankManager;
use App\Manager\RaidManager;
use App\Manager\GuildManager;
use App\Manager\MemberManager;
use App\Manager\RankGoalManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AllianceController extends BaseController
{
    private RankManager $_rankManager;
    private RaidManager $_raidManager;
    private GuildManager $_guildManager;
    private MemberManager $_memberManager;
    private RankGoalManager $_rankGoalManager;

    protected function __init($bag) {
        $this->_rankManager = $bag->get(RankManager::class);
        $this->_raidManager = $bag->get(RaidManager::class);
        $this->_guildManager = $bag->get(GuildManager::class);
        $this->_memberManager = $bag->get(MemberManager::class);
        $this->_rankGoalManager = $bag->get(RankGoalManager::class);
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $guilds = $this->_guildManager->getAll();
        $v_info = Array();
        try {
            $counts = $this->_memberManager->countMemberByGuildId();
            $ranks = $this->_rankManager->getAllGroupByGuildId();
            $objectives = $this->_rankGoalManager->getAll();
            $raids = $this->_raidManager->getDates();
        } catch (Exception $e) {
            $guilds = [];
            $this->addMsg("warning", $e->getMessage());
            $this->addMsg("warning", "erreur interne");
        }
        foreach ($guilds as $guildId => $guild) {
          $guildRank = null;
          if (array_key_exists($guildId, $ranks)) {
            $guildRank = end($ranks[$guildId])["rank"];
          }
            $v_info[$guildId] = Array(
                "guild" => ["name" => $guild->getName(), "color" => $guild->getColor()],
                "memberNumber" => $counts[$guildId],
                "ranks" => $ranks[$guildId],
                "lastRank" => $guildRank
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
                    return $this->redirect($response, ['alliance']);
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