<?php

namespace App\Controller;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

require_once('model/Manager/RaidManager.php');
use RaidManager;
require_once('model/Manager/FightManager.php');
use FightManager;
require_once('model/Manager/GuildManager.php');
use GuildManager;
require_once('model/Manager/BossManager.php');
use BossManager;
require_once('model/Manager/AilmentManager.php');
use AilmentManager;
require_once('model/Manager/WeaponManager.php');
use WeaponManager;
/*require_once('model/Manager/CharacterManager.php');
use CharacterManager;*/

final class RaidController extends BaseController
{
    private RaidManager $_raidManager;
    private FightManager $_fightManager;
    
    protected function __init() {
        //TODO inject instead
        $this->_raidManager = new RaidManager();
        $this->_fightManager = new FightManager();
    }

    public function info(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $bossManager = new BossManager();

        try {
            $id = $request->getQueryParams()['id'];
            $now = time();
            if (is_null($id)) {
                $raid = $this->_raidManager->getLastByDate();
                $raidId = $raid->getId();
                $raidDate = $raid->getDate();
            } else {
                $raidDate = $this->_raidManager->getDateById($id)->getDate();
                $raidId = $id;
            }
            $dPreview = $this->_raidManager->getPreviewDate();
            if (!is_null($dPreview)) {
                $raidPreviewId = $dPreview->getId();
            }
            $date2 = strtotime($raidDate);
            $diff = max(($now - $date2), 0);
            $dayNumber = floor((($diff / 60) / 60 ) / 24);
            $this->session->set('raidInfo', [
                "id" => $raidId,
                "dateRaid" => $raidDate,
                "dateNumber" => min($dayNumber, 13),
                "isFinished" => $dayNumber > 13
            ]);
        } catch (Exception $e) {
            $this->addMsg("danger", $e->getMessage());
        }

        $v_raids = [];
        try {
            foreach ($this->_raidManager->getDates() as $raid) {
              $v_raids[$raid->getId()] = $raid->getDate();
            }
        } catch (Exception $e) {
            $this->addMsg("danger", $e->getMessage());
        }

        $f_raidId = $raidPreviewId ?? $raidId;
        try {
            $v_raidInfo = $this->_raidManager->getById($f_raidId);

        } catch (Exception $e) {
            $this->addMsg("danger", $e->getMessage());
        }

        $b1 = $v_raidInfo->getBoss1Info();
        $b2 = $v_raidInfo->getBoss2Info();
        $b3 = $v_raidInfo->getBoss3Info();
        $b4 = $v_raidInfo->getBoss4Info();
        $v_bosses = [
            ['id' => $b1['id'], 'name' => $b1['name'], 'element' => $b1['e_name']],
            ['id' => $b2['id'], 'name' => $b2['name'], 'element' => $b2['e_name']],
            ['id' => $b3['id'], 'name' => $b3['name'], 'element' => $b3['e_name']],
            ['id' => $b4['id'], 'name' => $b4['name'], 'element' => $b4['e_name']]
        ];

        $v_ailments = [];
        try {
            $_ailments = (new AilmentManager)->getAll();
            foreach ($_ailments as $id => $ailment) {
                $weapons = [];
                foreach ((new WeaponManager())->getByAilmentBossRaid($id, $b1["id"], $b2["id"], $b3["id"], $b4["id"]) as $wid => $weapon) {
                    $rates = $weapon->getRates();
                    $charac = $weapon->getCharacInfo();
                    $weapons[$wid] = [
                        'name' => $weapon->getName(),
                        'rate1' => $rates['rate1'],
                        'rate2' => $rates['rate2'],
                        'rate3' => $rates['rate3'],
                        'rate4' => $rates['rate4'],
                        'charac' => ['id' => $charac['id'], 'name' => $charac['name']]
                    ];
                }

                $v_ailments[$id] = [
                    'name' => $ailment->getName(),
                    'weapons' => $weapons
                ];
            }
        } catch (Exception $e) {
            $this->addMsg("warning", $e->getMessage());
        }
        
        return $this->view->render($response, 'raid/info.twig', ['id' => $id, 'raids' => $v_raids, 'bosses' => $v_bosses, 'ailments' => $v_ailments]);
    }

    public function rank(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $id = $this->session->get('raidInfo')['id'];
        $raid = (new RaidManager())->getById($id);
        $v_fights = $this->_fightManager->getAllByRaid($raid->getDate());
        $v_bosses = [
            $raid->getBoss1Info()["id"] => $raid->getBoss1Info(),
            $raid->getBoss2Info()["id"] => $raid->getBoss2Info(),
            $raid->getBoss3Info()["id"] => $raid->getBoss3Info(),
            $raid->getBoss4Info()["id"] => $raid->getBoss4Info()
        ];

        $values = array();
        foreach ($v_fights as $pseudoId => $info) {
            $v_fights[$pseudoId]["guildColor"] = $this->getColor($info["guildColor"]);
            $bossInfo = $info["bosses"];
            $countFights = 0;
            $sumFights = 0;
            foreach ($bossInfo as $bossId => $fightInfo) {
                $damages = $fightInfo["damages"];
                $size = count($damages);
                if ($size > 1) {
                    $moy = array_sum($damages) / $size;
                    $subSum = 0;
                    foreach ($damages as $damage) {
                        $subSum += pow($damage - $moy, 2);
                    }
                    $sigma = sqrt($subSum / ($size - 1));
                } elseif ($size == 1) {
                    $moy = $damages[0];
                    $sigma = 0;
                }
                $keptDamages = Array();
                foreach ($damages as $damage) {
                    if ($this->isInRange($damage, ($moy - 2*$sigma), ($moy + 2*$sigma)) ) {
                        $keptDamages[] = $damage;
                    }
                }
                $v_fights[$pseudoId]["bosses"][$bossId]["Average"] = $moy;
                $v_fights[$pseudoId]["bosses"][$bossId]["Sigma"] = $sigma;
                $v_fights[$pseudoId]["bosses"][$bossId]["keptDamages"] = $keptDamages;
                $v_fights[$pseudoId]["bosses"][$bossId]["stats"] = [
                    'average' => number_format($moy, 0, ',', ' '),
                    'sigma' => number_format($sigma, 0, ',', ' ')
                ];

                $countFights += count($keptDamages);
                $sumFights += array_sum($keptDamages);
            }
            foreach ($v_fights[$pseudoId]['bosses'] as $bossId => $boss) {
                $nb = 3*count($v_fights[$pseudoId]["bosses"][$bossId]["keptDamages"])/$countFights;
                $v_fights[$pseudoId]["bosses"][$bossId]["stats"]['count'] = number_format($nb, 3, ',', ' ');
            }
            $v_fights[$pseudoId]["totalCount"] = $countFights;
            $v_fights[$pseudoId]["totalSum"] = $sumFights;
            $v_fights[$pseudoId]["average"] = number_format(round($sumFights/$countFights), 0, ',', ' ');
        }
        
        $sort = Array();
        $guildSort = Array();
        $bossSort = Array();
        foreach ((new GuildManager())->getAll() as $guildId => $guild) {
            $guildSort[$guild->getName()] = Array();
        }
        for ($i = 1; $i <= 4; $i++) {
            $bInfo = "getBoss".$i."Info";
            $boss = $raid->$bInfo();
            $bossSort[$boss["id"]] = Array();
        }
        foreach($v_fights as $pseudoId => $info) {
            $avg = $info["totalSum"]/$info["totalCount"];
            $sort[$pseudoId] = $avg;
            $guildSort[$info["guildName"]][$pseudoId] = $avg;
            foreach ($info["bosses"] as $bossId => $bossInfo) {
                $avg = $bossInfo["Average"];
                $bossSort[$bossId][$pseudoId] = $avg;
            }
        }
        arsort($sort);
        $i = 1;
        foreach ($sort as $pseudoId => $avg) {
            $v_fights[$pseudoId]["rankAlliance"] = $i++;
        }
        
        foreach ($guildSort as $guildName => $unused) {
            arsort($guildSort[$guildName]);
            $i = 1;
            foreach ($guildSort[$guildName] as $pseudoId => $avg) {
                $v_fights[$pseudoId]["rankGuild"] = $i++;
            }
        }
      
        $j = 1;
        foreach ($bossSort as $bossId => $unused) {
            arsort($bossSort[$bossId]);
            $i = 1;
            foreach ($bossSort[$bossId] as $pseudoId => $avg) {
                $v_fights[$pseudoId]["rankBoss$j"] = $i++;
            }
            $j++;
        }
        return $this->view->render($response, 'raid/rank.twig', ['fights' => $v_fights, 'bosses' => $v_bosses]);
    }

    private function isInRange($value, $min, $max) {
        if ($value < $min) {
          return false;
        } elseif ($value > $max) {
          return false;
        }
        return true;
      }
      
      private function getColor($guildColor) {
        if ($guildColor == "warning") {
          return "#ffc107aa";
        } elseif ($guildColor == "lightblue") {
          return "#3c8dbc66";
        } elseif ($guildColor == "lime") {
          return "#01ff7099";
        }
        return "#ffffff";
      }
      
  }