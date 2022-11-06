<?php

namespace App\Controller;

use Exception;
use App\Manager\RaidManager;
use App\Manager\TeamManager;
use App\Manager\GuildManager;
use App\Manager\FightManager;
use App\Manager\MemberManager;
use App\Manager\WeaponManager;
use App\Manager\AilmentManager;
use App\Manager\CharacterManager;
use App\Manager\RaidInfoManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\UploadedFile;
use Slim\Routing\RouteContext;

final class RaidController extends BaseController
{
    private RaidManager $_raidManager;
    private TeamManager $_teamManager;
    private FightManager $_fightManager;
    private GuildManager $_guildManager;
    private MemberManager $_memberManager;
    private WeaponManager $_weaponManager;
    private AilmentManager $_ailmentManager;
    private CharacterManager $_characterManager;
    private RaidInfoManager $_raidImageManager;
    
    protected function __init($bag) {
        $this->_raidManager = $bag->get(RaidManager::class);
        $this->_teamManager = $bag->get(TeamManager::class);
        $this->_fightManager = $bag->get(FightManager::class);
        $this->_guildManager = $bag->get(GuildManager::class);
        $this->_weaponManager = $bag->get(WeaponManager::class);
        $this->_memberManager = $bag->get(MemberManager::class);
        $this->_ailmentManager = $bag->get(AilmentManager::class);
        $this->_characterManager = $bag->get(CharacterManager::class);
        $this->_raidImageManager = $bag->get(RaidInfoManager::class);
    }
    
    static public function initialiseRaidInfo($session, $raidManager) {
      $raid = $raidManager->getLastByDate();
      
      $dateStart = strtotime($raid->getDate());
      $now = time();
      $diff = max(($now - $dateStart), 0);
      $dayNumber = floor((($diff / 60) / 60 ) / 24);
      $isFinished = $dayNumber > ($raid->getDuration()-1);
      
      $preview = $raidManager->getPreviewDate();
      if (!is_null($preview)) {
        $session->set('raidPreview', [
            'id' => $preview->getId(),
            'dateRaid' => $preview->getDate(),
            'usePreview' => $isFinished,
        ]);
      } else {
        $session->set('raidPreview', NULL);
      }
      
      $session->set('raidInfo', [
          "id" => $raid->getId(),
          "dateRaid" => date("Y-m-d", $dateStart),
          "duration" => $raid->getDuration(),
          "dateNumber" => min($dayNumber, ($raid->getDuration()-1)),
          "isFinished" => $isFinished,
      ]);
    }
    
    private function updateRaidInfo($session, $raidManager, $raidId) {
      $raid = $raidManager->getDateById($raidId);
      if (!is_null($this->session->get('raidPreview')))
      {
        $preview = $session->get('raidPreview');
        if ($this->session->get('raidPreview')['id'] == $raidId) {
          $preview['usePreview'] = true;
          $session->set('raidPreview', $preview);
          $raid = $raidManager->getDateById($this->session->get('raidInfo')['id']);
        } else {
          $preview['usePreview'] = false;
          $session->set('raidPreview', $preview);
        }
      }
      
      $dateStart = strtotime($raid->getDate());
      $now = time();
      $diff = max(($now - $dateStart), 0);
      $dayNumber = floor((($diff / 60) / 60 ) / 24);
      $isFinished = $dayNumber > ($raid->getDuration()-1);
      
      $session->set('raidInfo', [
          "id" => $raid->getId(),
          "dateRaid" => date("Y-m-d", $dateStart),
          "duration" => $raid->getDuration(),
          "dateNumber" => min($dayNumber, ($raid->getDuration()-1)),
          "isFinished" => $isFinished,
      ]);
    }

    public function info(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        if ($request->getMethod() === "POST") {
          $form = $request->getParsedBody();
          $raidId = $form['raidId'];
          $this->updateRaidInfo($this->session, $this->_raidManager, $raidId);
          $usePreview = $this->session->get('raidPreview')['usePreview'];
        } else {
          $preview = $this->session->get('raidPreview');
          $usePreview = (!is_null($preview) and $preview['usePreview']);
          $raidId = $usePreview ? $preview['id'] : $this->session->get('raidInfo')['id'];
        }

        $raidDates = Array();
        foreach ($this->_raidManager->getDates() as $raid) {
          $raidDates[$raid->getId()] = $raid->getDate();
        }
        
        try {
            $raidInfo = $this->_raidManager->getById($raidId);
        } catch (Exception $e) {
            $this->addMsg("danger", $e->getMessage());
        }

        $b1 = $raidInfo->getBoss1Info();
        $b2 = $raidInfo->getBoss2Info();
        $b3 = $raidInfo->getBoss3Info();
        $b4 = $raidInfo->getBoss4Info();
        $bosses = [$b1, $b2, $b3, $b4];

        $v_ailments = [];
        try {
            $_ailments = $this->_ailmentManager->getAll();
            foreach ($_ailments as $id => $ailment) {
                $weapons = [];
                foreach ($this->_weaponManager->getByAilmentBossRaid($id, $b1["id"], $b2["id"], $b3["id"], $b4["id"]) as $wid => $weapon) {
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
        
        $infos = Array();
        $imgRaidPath = $this->configDirPath->get('imageRelative').$this->configDirPath->get('raidInfo');
        foreach ($this->_raidImageManager->getAllByRaid($raidId) as $infoId => $info) {
          if ($info->getType() == "image") {
            $infos[] = ["source" => $imgRaidPath.$infoId.".".$info->getExtension(),
                        "imgSrc" => $imgRaidPath.$infoId.".".$info->getExtension(),
                        "originalName" => $info->getSource()];
          } else if ($info->getType() == "video") {
            $infos[] = ["source" => $info->getSource(),
                        "imgSrc" => $imgRaidPath."video.png",
                        "originalName" => ""];
          }
        }
        
        return $this->view->render($response, 'raid/info.twig',
                ['raidId' => $raidId,
                 'raids' => $raidDates,
                 'isPreview' => $usePreview,
                 'bosses' => $bosses,
                 'ailments' => $v_ailments,
                 'imgHeros' => $this->configDirPath->get('imageRelative').$this->configDirPath->get('heros'),
                 'imgRaidPath' => $imgRaidPath,
                 'infos' => $infos]);
    }

    public function addInfo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
      $form = $request->getParsedBody();
      $directory = $this->configDirPath->get('imageRoot').$this->configDirPath->get('raidInfo');
      $raidId = $form['raidIdForm'];

      $uploadedFiles = $request->getUploadedFiles();
      $images = $this->_raidImageManager->getAllByRaid($raidId);
      
      $infoId = empty($images) ? 0 : max(array_keys($images))+1;
      foreach ($uploadedFiles["files"] as $uploadedFile) {
        print("file= ");print_r($uploadedFile);print("<br>");
        if ($uploadedFile->getError() === UPLOAD_ERR_OK
            and str_contains($uploadedFile->getClientMediaType(), 'image')) {
          $type = 'image';
          $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
          $source = pathinfo($uploadedFile->getClientFilename(), PATHINFO_BASENAME);
          if ($this->_raidImageManager->addEntity($raidId, $infoId, $type, $extension, $source)) {
            $uploadedFile->moveTo($directory.$infoId.".".$extension);
            $infoId+=1;
          } else {

          }
        }
      }
      if (!empty($form["videoLinkForm"])) {
        $type = 'video';
        $extension = "";
        $source = $form["videoLinkForm"];
        $this->_raidImageManager->addEntity($raidId, $infoId, $type, $extension, $source);
      }
      return $this->redirect($response, ['raid-info']);
    }

    public function rank(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $defaultBoss = Array("id" => 0, "name" => "--- default ---", "shortName" => "default", "element" => 0, "e_name" => "");
        $id = $this->session->get('raidInfo')['id'];
        $raid = $this->_raidManager->getById($id);
        $v_fights = $this->_fightManager->getAllByRaid($raid->getDate());
        $v_bosses = [
            NULL => $defaultBoss,
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
        foreach ($this->_guildManager->getAll() as $guildId => $guild) {
            $guildSort[$guild->getName()] = Array();
        }
        $bossSort[NULL] = Array();
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

    public function meteo(ServerRequestInterface $request, ResponseInterface $response, string $guildId = null): ResponseInterface {
        $info = $this->session->get('raidInfo');
        $dateRaid = $info["dateRaid"];
        $plusDays = $info["dateNumber"] + ($info["isFinished"] ? 0 : -1);
        $date = date("Y-m-d", strtotime("$dateRaid +".max($plusDays,0)." day"));
        $v_meteo = Array();
        if (is_null($guildId)) $guildId = $this->session->get('guild')['id'];

        if ($plusDays >= 0) {
            try {
                $counts = $this->_fightManager->getCountByGuildIdDate($guildId, $date);
            } catch (Exception $e) {
                $this->addMsg("danger", $e->getMessage());
            }
            $members = $this->_memberManager->getAllByGuildIdInRawData($guildId, $date, false);
            $i = 0;
            foreach ($members as $memberId => $memberName) {
                if (array_key_exists($memberId, $counts)) {
                    $c = $counts[$memberId]["counter"];
                    $d = $counts[$memberId]["damages"];
                } else {
                    $c = 0;
                    $d = 0;
                }
                $v_meteo[$i++] = Array("memberId" => $memberId,
                                    "memberName" => $memberName,
                                    "count" => $c,
                                    "dailyDamage" => $d
                );
            }
        }

        return $this->view->render($response, 'raid/meteo.twig', ['title' => "Météo du ".$date, 'meteo' => $v_meteo]);
    }

    public function followup(ServerRequestInterface $request, ResponseInterface $response, string $guildId = null): ResponseInterface {
        if (is_null($guildId)) $guildId = $this->session->get('guild')['id'];
        $raidInfo = $this->session->get('raidInfo');

        if (is_null($raidInfo["dateRaid"])) {
            try {
                $dateRaid = $this->_raidManager->getLastByDate()->getDate();
                $raidDuration = $this->_raidManager->getLastByDate()->getDuration();
            } catch (Exception $e) {
                $this->addMsg("danger", $e->getMessage());
            }
            $yesterday = date("Y-m-d", strtotime(date("Y-m-d")." -1 day"));
            $dayNumber = (DateTime::createFromFormat("Y-m-d", $yesterday)->diff(DateTime::createFromFormat("Y-m-d", $dateRaid)))->d;
        } else {
            $dateRaid = $raidInfo["dateRaid"];
            $dayNumber = $raidInfo["dateNumber"];
            $raidDuration = $raidInfo["duration"];
        }
        $v_damagesByMemberByDay = Array();
        if ($dayNumber >= 0) {
            try {
                $datePrevRaid = $this->_raidManager->getLastByDate(date("Y-m-d", strtotime("$dateRaid -1 day")))->getDate();
                $noPrev = false;
            } catch (Exception $e) {
                $noPrev = true;
                $datePrevRaid = date("Y-m-d", strtotime("$dateRaid -1 day"));
                $this->addMsg("info", "pas de résultat pour le raid précédent");
            }
            try {
                $fightsByMemberByDay = $this->_fightManager->getAllByGuildIdDateGroupByPseudoIdDate($guildId, $dateRaid);
                $prevFightsByMemberByDay = $this->_fightManager->getAllByGuildIdDateGroupByPseudoIdDate($guildId, $datePrevRaid);
            } catch (Exception $e) {
                $this->addMsg("danger", $e->getMessage());
            }
            if ($noPrev) {
                $prevFightsByMemberByDay = [];
            }

            // traitement de la requête et reconstruction du tableau
            $v_damagesByMemberByDay = Array();
            $members = $this->_memberManager->getAllWithDateStartByGuildIdInRawData($guildId, date('Y-m-d'), false);// TODO get id, name et dateStart pour pas check la diff si arrivé en cours de raid
            $i = -1;
            $v_globalSum = 0;
            $d = date("Y-m-d", strtotime("$dateRaid +$dayNumber day"));
            $prevDay = $raidInfo["isFinished"] ? ($raidDuration-1) : max($dayNumber -1, 0);
            foreach ($members as $memberId => $memberInfo) {
                $memberName = $memberInfo["name"];
                $memberDateStart = $memberInfo["dateStart"];
                ++$i;
                if (array_key_exists($memberId, $fightsByMemberByDay)) {
                    $v_damagesByMemberByDay[$i] = Array("memberId" => $memberId, "memberName" => $memberName);
                    $sum = 0;
                    $sumPrev = 0;
                    $date1 = array_key_first($fightsByMemberByDay[$memberId]);
                    if (!is_null($date1)) {
                        $date1 = strtotime($date1);
                        $date2 = strtotime($dateRaid);
                        $diff = max(($date1 - $date2), 0);
                        $endFor = floor((($diff / 60) / 60 ) / 24);
                    } else {
                        $endFor = -1;
                    }
                    for ($j = 0; $j <= $endFor; $j++) {
                        $d = date("Y-m-d", strtotime("$dateRaid +$j day"));
                        $dPrev = date("Y-m-d", strtotime("$datePrevRaid +$j day"));
                        if (array_key_exists($d, $fightsByMemberByDay[$memberId]) and ($memberDateStart <= $d)) {
                            $dailySum = $fightsByMemberByDay[$memberId][$d];
                            $sum += $dailySum;
                        } elseif ($memberDateStart > $d) {
                            $dailySum = NULL;
                        } else {
                            $dailySum = 0;
                        }
                        $v_damagesByMemberByDay[$i]["day$j"] = $dailySum;
                        if (array_key_exists($memberId, $prevFightsByMemberByDay) and ($memberDateStart <= $dPrev)) {
                            if (array_key_exists($dPrev, $prevFightsByMemberByDay[$memberId])) {
                                $dailySumPrev = $prevFightsByMemberByDay[$memberId][$dPrev];
                            } else {
                                $dailySumPrev = 0;
                            }
                            $sumPrev += $dailySumPrev;
                        } else {
                            $dailySumPrev = NULL;
                            $sumPrev = 0;
                        }
                        if ($j == $prevDay) {
                            $v_damagesByMemberByDay[$i]["yesterdaySum"] = $sum;
                            $v_damagesByMemberByDay[$i]["yesterdaySumPrev"] = ($sumPrev == 0 ? NULL : $sumPrev);
                        }
                        $v_damagesByMemberByDay[$i]["day$j"."Prev"] = $dailySumPrev;
                    }
                    for (; $j < $raidDuration; $j++) {
                        $v_damagesByMemberByDay[$i]["day$j"] = 0;
                        $v_damagesByMemberByDay[$i]["day$j"."Prev"] = 0;
                        if ($j == $prevDay) {
                            $v_damagesByMemberByDay[$i]["yesterdaySum"] = $sum;
                            $v_damagesByMemberByDay[$i]["yesterdaySumPrev"] = ($sumPrev == 0 ? NULL : $sumPrev);
                        }
                    }
                    $v_globalSum += $sum;
                    $v_damagesByMemberByDay[$i]["daysSum"] = $sum;
                    $v_damagesByMemberByDay[$i]["daysSumPrev"] = ($sumPrev == 0 ? NULL : $sumPrev);
                } else {//$raidDuration
                    $v_damagesByMemberByDay[$i] = array("memberId" => $memberId, "memberName" => $memberName,
                                                        "daysSum" => NULL, "yesterdaySum" => 0,
                                                        "daysSumPrev" => NULL, "yesterdaySumPrev" => NULL);
                    for ($j = 0; $j < $raidDuration; $j++) {
                      $v_damagesByMemberByDay[$i]["day$j"] = NULL;
                      $v_damagesByMemberByDay[$i]["day$j"."Prev"] = NULL;
                    }
                    
                }
            }
        }
        $toSort = Array();
        foreach ($v_damagesByMemberByDay as $key => $damagesByDay) {
            $toSort[$key] = $damagesByDay["yesterdaySum"];
        }
        arsort ($toSort, SORT_NUMERIC );
        $temp = Array();
        foreach ($toSort as $key => $unused) {
            $temp[] = $v_damagesByMemberByDay[$key];
        }
        $v_damagesByMemberByDay = $temp;
        $v_prevDayNumber = $prevDay+1;

        return $this->view->render($response, 'raid/followup.twig', [
            'title' => "Suivi du raid, score actuel= ".number_format($v_globalSum, 0, ',', ' '),
            'damages' => $v_damagesByMemberByDay,
            'prevDay' => $v_prevDayNumber,
            'raidDuration' => $raidDuration,
        ]);
    }

    public function miss(ServerRequestInterface $request, ResponseInterface $response, string $guildId = null): ResponseInterface {
        if (is_null($guildId)) $guildId = $this->session->get('guild')['id'];
        $info = $this->session->get('raidInfo');

        if (is_null($info["dateRaid"])) {
            try {
                $dateRaid = $this->_raidManager->getLastByDate()->getDate();
            } catch (Exception $e) {
                $this->addMsg("danger", $e->getMessage());
            }
            $yesterday = date("Y-m-d", strtotime(date("Y-m-d")." -1 day"));
            $dayNumber = (DateTime::createFromFormat("Y-m-d", $yesterday)->diff(DateTime::createFromFormat("Y-m-d", $dateRaid)))->d;
        } else {
            $dateRaid = $info["dateRaid"];
            $dayNumber = $info["dateNumber"] + ($info["isFinished"] ? 0 : -1);
        }
        $v_missedByMemberByDay = Array();
        if ($dayNumber >= 0) {
            try {
                $missByMemberByDay = $this->_fightManager->getAllByGuildIdDateGroupByTeamNumberDate($guildId, $dateRaid);
            } catch (Exception $e) {
                $v_error = $e->getMessage();
            }
            $v_guilds = $this->_guildManager->getAll();
            $f_guildId = $guildId;

            $members = $this->_memberManager->getAllWithDateStartByGuildIdInRawData($guildId, date('Y-m-d'), false);// TODO get id, name et dateStart pour pas check la diff si arrivé en cours de raid
            foreach ($missByMemberByDay as $memberId => $missedByDate) {
                if (!isset($members[$memberId]) or !isset($members[$memberId]["name"])) { continue;}
                $member = $members[$memberId]["name"];
                $v_missedByMemberByDay[$member] = Array();
                $memberDateStart = $members[$memberId]["dateStart"];
                $noMiss = true;
                for ($j = 0; $j <= $dayNumber; $j++) {
                    $d = date("Y-m-d", strtotime("$dateRaid +$j day"));
                    if (array_key_exists($d, $missedByDate) and ($memberDateStart <= $d)) {
                        $miss = $missedByDate[$d];
                    } elseif ($memberDateStart > $d) {
                        $v_missedByMemberByDay[$member]["color$j"] = true;
                        $miss = NULL;
                    } else {
                        $miss = 0;
                    }
                    if (($miss == 3) or is_null($miss)) {
                        $miss = NULL;
                    } else {
                        $miss = "$miss /3";
                        $noMiss = false;
                    }
                    $v_missedByMemberByDay[$member]["day$j"] = $miss;
                }
                $v_missedByMemberByDay[$member]["noMiss"] = $noMiss;
                for (; $j < 14; $j++) {
                    $v_missedByMemberByDay[$member]["day$j"] = NULL;
                }
            }
            ksort($v_missedByMemberByDay);
        }
        return $this->view->render($response, 'raid/miss.twig', ['title' => "Les absences", 'misses' => $v_missedByMemberByDay]);
    }

    public function summary(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        try {
            $fights = [];
            foreach ($this->_fightManager->getAll() as $fight) {
                $fights[] = [
                    'pseudo' => $fight->getPseudoInfo()["name"],
                    'guild' => $fight->getGuildInfo()["name"],
                    'date' => $fight->getDate(),
                    'team' => $fight->getTeamNumber(),
                    'boss' => $fight->getBossInfo()["name"],
                    'damage' => $fight->getDamage(),
                    'hero1' => $fight->getHero1Info()["name"],
                    'hero2' => $fight->getHero2Info()["name"],
                    'hero3' => $fight->getHero3Info()["name"],
                    'hero4' => $fight->getHero4Info()["name"]
                ];
            }
        } catch (Exception $e) {
            $this->addMsg("danger", $e->getMessage());
        }
        return $this->view->render($response, 'raid/summary.twig', ['fights' => $fights]);
    }

    public function fights(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $guildId = $this->session->get('guild')['id'];
        $raidInfo = $this->session->get('raidInfo');
        
        $today = $raidInfo['dateNumber'];
        $dateNumber = $this->getDateNumber(
                $request->getQueryParams()['dateNumber'],
                $today);
        $date = date("Y-m-d", strtotime($raidInfo["dateRaid"]." +$dateNumber day"));//date("Y-m-d");
        $isJoueur = $this->session->get('grade') == $this->session->get('Joueur');
    
        if ($request->getMethod() === 'POST') {
            $form = $request->getParsedBody();
            $isInsert  = isset($form["teamAddForm"]);
            $isUpdate  = isset($form["teamUpdateForm"]);
            $isDeleted  = isset($form["teamDeleteForm"]);
            $isTeamSaved = $form["saveTeamForm"];
        
            if ($isInsert or $isUpdate) {
                if (!$this->checkFight($form["bossForm"], $form["damageForm"])) {
                    $isInsert = false;
                    $isUpdate = false;
                }
            }
            $hasError = false;
            if ($isJoueur
                    and ($isInsert or $isUpdate or $isDeleted)
                    and ($this->session->get('id') != $form["recorderIdForm"])) {
                $hasError = true;
            } elseif (($isInsert or $isUpdate or $isDeleted)
                    and (strtotime($date) < strtotime($raidInfo["dateRaid"]))) {
                $hasError = true;
            } else {
                // Ok on pas faire les mise à jour bdd
                if ($isInsert) {
                    $boss = $form["bossForm"];
                    $hero1 = $form["hero1Form"];
                    $hero2 = $form["hero2Form"];
                    $hero3 = $form["hero3Form"];
                    $hero4 = $form["hero4Form"];
                    $this->submitFight(
                        $form["recorderIdForm"],
                        $form["guildForm"],
                        $form["memberForm"],
                        $form["raidIdForm"],
                        $form["dateForm"],
                        $form["teamForm"],
                        $boss == 0 ? NULL : $boss,
                        $form["damageForm"],
                        $form["isExtraForm"],
                        $hero1 == 0 ? NULL : $hero1,
                        $hero2 == 0 ? NULL : $hero2,
                        $hero3 == 0 ? NULL : $hero3,
                        $hero4 == 0 ? NULL : $hero4
                    );
                } elseif ($isUpdate or $isDeleted) {
                    $boss = $isUpdate ? $form["bossForm"] : 0;
                    $damage = $isUpdate ? $form["damageForm"] : NULL;
                    $hero1 = $form["hero1Form"];
                    $hero2 = $form["hero2Form"];
                    $hero3 = $form["hero3Form"];
                    $hero4 = $form["hero4Form"];
                    $deleted = $isDeleted ? 1 : 0;
                    $this->saveFight(
                        $form["recorderIdForm"],
                        $form["idForm"],
                        $form["guildForm"],
                        $form["memberForm"],
                        $form["raidIdForm"],
                        $form["dateForm"],
                        $form["teamForm"],
                        $boss == 0 ? NULL : $boss,
                        $damage,
                        $form["isExtraForm"],
                        $hero1 == 0 ? NULL : $hero1,
                        $hero2 == 0 ? NULL : $hero2,
                        $hero3 == 0 ? NULL : $hero3,
                        $hero4 == 0 ? NULL : $hero4,
                        $deleted
                    );
                }
                if ($isTeamSaved == "on") {
                    $this->addOrUpdateTeam(
                        $form["memberForm"],
                        $form["teamNbUsedForm"],
                        $form["hero1Form"],
                        $form["hero2Form"],
                        $form["hero3Form"],
                        $form["hero4Form"]
                    );
                }
            }
        }

        $filter = NULL;
        if ($isJoueur) {
          $filter = $this->session->get('id');
        }
    
        $f_date = $date;
        $f_raid = $this->_raidManager->getById($raidInfo["id"]);
        if ($hasError) {
            $this->addMsg("warning", "Il y a eu un problème pendant le traitement de votre requête");
        }

        $members = $this->_memberManager->getAllByGuildId($guildId, $filter);
        $teams = $this->_memberManager->getTeamsByGuild($guildId, $filter);
        $fights = $this->_fightManager->getAllByGuildIdDate($guildId, $date, 0, $filter);
        $extraFights = $this->_fightManager->getAllByGuildIdDate($guildId, $date, 1, $filter);

        $v_fights = [];
        foreach($members as $memId => $member) {
            $v_fights[$memId] = ["member" => $member->getName(),
                                 "guildId" => $guildId,
                                 "savedTeams" => [],
                                 "fights" => [],
                                 "extraFights" => []
                                ];
            if (!array_key_exists($memId, $teams)) {
                $v_fights[$memId]["teamIds"] = '';
                $v_fights[$memId]["savedTeams"] = [];
            } else {
                $teamsByMember = $teams[$memId]["teams"];
                $v_fights[$memId]["teamIds"] = implode("-", array_keys($teamsByMember));
                foreach ($teamsByMember as $teamNb => $team) {
                    $v_fights[$memId]["savedTeams"][$teamNb] = 
                        ["id" => $team->getId(),
                        "teamNumber" => $team->getTeamNumber(),
                        "heros" => $team->getHero1Id()."-"
                                    .$team->getHero2Id()."-"
                                    .$team->getHero3Id()."-"
                                    .$team->getHero4Id()];
                }
            }
            if (!array_key_exists($memId, $fights)) {
                $v_fights[$memId]["fights"] = [];
            } else {
                $fightsByMember = $fights[$memId]["fights"];
                foreach ($fightsByMember as $fightNb => $fight) {
                    $v_fights[$memId]["fights"][$fightNb] = [
                        'id' => $fight->getId(),
                        'guild' => $fight->getGuildInfo()["id"],
                        'raid' => $fight->getRaidId(),
                        'date' => $fight->getDate(),
                        'boss' => $fight->getBossInfo()["id"],
                        'damage' => $fight->getDamage(),
                        'hero1' => $fight->getHero1Info()["id"],
                        'hero2' => $fight->getHero2Info()["id"],
                        'hero3' => $fight->getHero3Info()["id"],
                        'hero4' => $fight->getHero4Info()["id"]
                    ];
                }
            }
            if (!array_key_exists($memId, $extraFights)) {
                $v_fights[$memId]["extraFights"] = [];
            } else {
                $fightsByMember = $extraFights[$memId]["fights"];
                foreach ($fightsByMember as $fightNb => $fight) {
                    $v_fights[$memId]["extraFights"][$fightNb] = [
                        'id' => $fight->getId(),
                        'guild' => $fight->getGuildInfo()["id"],
                        'raid' => $fight->getRaidId(),
                        'date' => $fight->getDate(),
                        'boss' => $fight->getBossInfo()["id"],
                        'damage' => $fight->getDamage(),
                        'hero1' => $fight->getHero1Info()["id"],
                        'hero2' => $fight->getHero2Info()["id"],
                        'hero3' => $fight->getHero3Info()["id"],
                        'hero4' => $fight->getHero4Info()["id"]
                    ];
                }
            }
        }

        $v_characters = $this->_characterManager->getAllOrderByGradeElementName();
        $characters = [];
        foreach ($v_characters as $character) {
            $g = $character->getGrade();
            if (!isset($characters[$g])) $characters[$g] = ['name' => $g, 'elements' => []];
            $e = $character->getElementInfo();
            if (!isset($characters[$g]['elements'][$e['id']])) $characters[$g]['elements'][$e['id']] = ['name' => $e['name'], 'characters' => []];
            $characters[$g]['elements'][$e['id']]['characters'][$character->getId()] = $character->getName();
        }
        
        for ($i = 0; $i < $raidInfo['duration']; $i++) {
          $dates[] = strtotime($raidInfo['dateRaid']."+$i day");
        }
        
        return $this->view->render($response, 'raid/fights.twig', [
            'title' => "Raid du ".date("d F", strtotime($raidInfo['dateRaid']))." - les attaques",
            'guildId' => $guildId,
            'filter' => $filter,
            'members' => $v_fights,
            'dateNumber' => $dateNumber,
            'dates' => $dates,
            'today' => $today,//$guildId = $this->session->get('raidInfo')['datenumber'],
            'date' => $f_date,
            'raid' => [
                'id' => $f_raid->getId(),
                'bosses' => [
                    $f_raid->getBoss1Info()["id"] => $f_raid->getBoss1Info()["name"],
                    $f_raid->getBoss2Info()["id"] => $f_raid->getBoss2Info()["name"],
                    $f_raid->getBoss3Info()["id"] => $f_raid->getBoss3Info()["name"],
                    $f_raid->getBoss4Info()["id"] => $f_raid->getBoss4Info()["name"]                    
                ]
                ],
                'characters' => $characters
        ]);
    }
    
    private function getDateNumber($dateFromQuery, $dateFromSession) : int {
      if (isset($dateFromQuery) and $dateFromQuery <= $dateFromSession) {
        return $dateFromQuery;
      }
      return $dateFromSession;
    }

    public function fightsEnd(ServerRequestInterface $request, ResponseInterface $response, string $guildId): ResponseInterface {
        if ($this->session->get("grade") > $this->session->get("Gestion")) return $this->redirect($response, ['403']);

        $lastRaidDate = $this->_raidManager->getLastByDate()->getDate();
        $fights = $this->_fightManager->getAllByRaidAndGuild($lastRaidDate, $guildId);
        $days = [];
        $total = 0;
        foreach ($fights as &$member) {
            $teams = [];
            $battles = [];
            $damage = 0;
            $count = 0;
            foreach ($member['days'] as $k => $day) {
                $days[] = $k;
                foreach ($day as $fight) {
                    $count++;
                    $key = implode('-', $fight['team']);
                    $damage += $fight['damage'];
                    if (!isset($teams[$key])) {
                        $teams[$key] = [$k];
                        $battles[$key] = ['damage' => $fight['damage'], 'bosses' => [$fight['boss'] => 1]];
                    }
                    else {
                        $teams[$key][] = $k;
                        $battles[$key]['damage'] += $fight['damage'];
                        if (isset($battles[$key]['bosses'][$fight['boss']])) $battles[$key]['bosses'][$fight['boss']]++;
                        else $battles[$key]['bosses'][$fight['boss']] = 1;
                    }
                }
            }
            arsort($teams);
            $member['teams'] = [];
            $member['damage'] = $damage;
            $member['count'] = $count;
            $total += $damage;
            foreach ($teams as $key => $dates) {
                //if a team have been used only once, it is very likely that it was a "day 1" team, so we don't include it
                if (count($dates) < 2) continue;
                $team = explode('-', $key);
                $found = false;
                foreach ($member['teams'] as $t) {
                    foreach ($team as $hero) {
                        $found = $found || in_array($hero, $t['team']);
                    }
                }
                if (!$found)
                {
                    arsort($battles[$key]['bosses']);
                    $member['teams'][] = [
                        'team' => $team,
                        'damage' => round($battles[$key]['damage'] / $count),
                        'boss' => array_key_first($battles[$key]['bosses'])
                        //'days' => $dates
                    ];
                }
            }
        }
        $days = array_unique($days);
        sort($days);
        uasort($fights, fn($a, $b) => $b['damage'] - $a['damage']);
        //We remove the first team day suggestion
        /*$firstDay = $days[0];
        foreach ($fights as &$member) {
            $newTeams = [];
            foreach ($team as $member['teams']) {
                if (count($team['days']) > 1 || $team['days'][0] != $firstDay) {
                    $newTeams[] = $team;
                }
            }
            $member['teams'] = $newTeams;
        }*/

        return $this->view->render($response, 'raid/fights-end.twig', [
            'title' => 'Aide pour completion du raid',
            'fights' => $fights,
            'days' => array_flip($days),
            'total' => $total
        ]);
    }

    public function fightsEndSave(ServerRequestInterface $request, ResponseInterface $response, string $guildId): ResponseInterface {
        if ($this->session->get("grade") > $this->session->get("Gestion")) return $this->redirect($response, ['403']);
        $recorder = $this->session->get("id");
        $raid = $this->_raidManager->getLastByDate()->getId();
        $body = $request->getParsedBody();
        $member = $body['member'];
        $suggestions = json_decode($body['suggestion']);
        foreach ($suggestions as $day) {
            foreach ($day->battles as $i => $battle) {
                $this->_fightManager->addFight($member, $guildId, $raid, $day->day, $day->slots[$i], $battle->boss,
                    $battle->weightedDamage, $battle->team[0], $battle->team[1], $battle->team[2], $battle->team[3], $recorder);
            }
        }

        return $this->redirect($response, ['raid-fights-end', ['guildId' => $guildId]]);
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
      
    private function checkDate($date) {
        if ($date > date("Y-m-d")) {
            $this->addMsg("warning", "Vous ne pouvez pas anticiper une attaque");
            return false;
        }
        return true;
    }

    private function checkFight($bossId, $damage) {
        if(!is_numeric($damage) or is_null($damage) or (intval($damage) < 0)) {
            $this->addMsg("warning", "Les dégâts doivent être positif");
            return false;
        }
        return true;
    }

    private function submitFight($recorderId, $guildId, $memberId, $raidId, $date, 
            $teamNumber, $bossId, $damage, $isExtra,
            $hero1Id, $hero2Id, $hero3Id, $hero4Id) {
        if ($bossId < 0 or is_null($damage)) {
            $this->addMsg("warning", "Selectionner un boss et définisser des dommages");
            return;
        }
        try {
            if (!$this->_fightManager->isExist($memberId, $date, $teamNumber, $isExtra)) {
                try {
                    $this->_fightManager->addFight($memberId, $guildId, $raidId, $date,
                                                  $teamNumber, $bossId, $damage, $isExtra,
                                                  $hero1Id, $hero2Id, $hero3Id, $hero4Id, $recorderId);
                    $this->addMsg("success", "Attaque enregistrée");
                } catch (Exception $e) {
                    $this->addMsg("danger", $e->getMessage());
                }
            }
        } catch (Exception $e) {
            $this->addMsg("danger", $e->getMessage());
        }

    }

    private function saveFight($recorderId, $id, $guildId, $memberId, $raidId, $date,
            $teamNumber, $bossId, $damage, $isExtra,
            $hero1Id, $hero2Id, $hero3Id, $hero4Id, $deleted) {
        if ($this->_fightManager->checkFight($id, $guildId, $memberId, $date, $teamNumber, $isExtra)) {
            if($this->_fightManager->updateFight($id, $bossId, $damage, $isExtra, $hero1Id, $hero2Id, $hero3Id, $hero4Id, $recorderId, $deleted)){
                    $this->addMsg("success", "Attaque modifiée");
            }
        }
    }

    private function addOrUpdateTeam($memberId, $teamNumber, $hero1Id, $hero2Id, $hero3Id, $hero4Id) {
        $id = $this->_teamManager->getTeamId($memberId, $teamNumber);
        if ($id != 0) {
            $editParams = Array("hero1Id" => $hero1Id,
                "hero2Id" => $hero2Id,
                "hero3Id" => $hero3Id,
                "hero4Id" => $hero4Id);
            $this->_teamManager->updateTeamNyMemberAndTeam($id, $editParams);
        } else {
            $this->_teamManager->addTeam($memberId, $teamNumber, $hero1Id, $hero2Id, $hero3Id, $hero4Id);
        }
    }
    
    public function finalise(ServerRequestInterface $request, ResponseInterface $response, string $guildId = null): ResponseInterface {
        if (is_null($guildId)) $guildId = $this->session->get('guild')['id'];
        $guildId = $this->session->get('guild')['id'];
        $raidInfo = $this->session->get('raidInfo');
        
        if (($request->getMethod() === 'POST')) {
          $form = $request->getParsedBody();
          $memberIdForm = $form["memberIdForm"];
          $memberFights = $this->_fightManager->getAllByGuildIdGroupByPseudoIdDateTeamNumber($guildId, $raidInfo["id"], $memberIdForm);
          $fights = array_key_exists($memberIdForm, $memberFights) ? $memberFights[$memberIdForm] : Array();
          $fightNbToAdd = min($raidInfo["duration"]*3, $form["nbFightForm"]) - count($fights);
          $fightDamageToAdd = $form["damageForm"] - array_sum(array_column($fights, "sum"));
          $firstAdd = true;
          if (count($fights) > 0 and $fightNbToAdd == 0) {
            $rest = $fightDamageToAdd % count($fights);
            $avg = ($fightDamageToAdd - $rest) / count($fights);
            foreach ($fights as $fight) {
              $damage = $firstAdd ? $avg + $rest : $avg;
              $this->_fightManager->updateFightDamage($fight["id"], $fight["damage"] + $damage, $this->session->get('id'));
              $firstAdd = false;
            }
          } else {
            $rest = $fightDamageToAdd % $fightNbToAdd;
            $avg = ($fightDamageToAdd - $rest) / $fightNbToAdd;
            $dateIdx = 0;
            $teamIdx = 1;
            $nbAdded = 0;
            foreach ($fights as $fight) {
              $d = date("Y-m-d", strtotime($raidInfo["dateRaid"]." +$dateIdx day"));
              while ($fight["date"] != $d or $fight["teamNumber"] != $teamIdx) {
                if ($nbAdded < $fightNbToAdd) {
                  $damage = $firstAdd ? $avg + $rest : $avg;
                  $this->_fightManager->addFight($memberIdForm, $guildId, $raidInfo["id"], $d, $teamIdx, 0, $damage,
                                                 0, 0, 0, 0, 0, $this->session->get('id'));
                  $firstAdd = false;
                  $nbAdded+=1;
                }
                $teamIdx+=1;
                if ($teamIdx === 4) {
                  $teamIdx = 1;
                  $dateIdx+=1;
                  $d = date("Y-m-d", strtotime($raidInfo["dateRaid"]." +$dateIdx day"));
                }
              }
              $teamIdx+=1;
              if ($teamIdx === 4) {
                $teamIdx = 1;
                $dateIdx+=1;
                $d = date("Y-m-d", strtotime($raidInfo["dateRaid"]." +$dateIdx day"));
              }
            }
            while ($nbAdded < $fightNbToAdd) {
              $d = date("Y-m-d", strtotime($raidInfo["dateRaid"]." +$dateIdx day"));
              $damage = $firstAdd ? $avg + $rest : $avg;
              $this->_fightManager->addFight($memberIdForm, $guildId, $raidInfo["id"], $d, $teamIdx, 0, $damage,
                                             0, 0, 0, 0, 0, $this->session->get('id'));
              $firstAdd = false;
              $nbAdded+=1;
              $teamIdx+=1;
              if ($teamIdx === 4) {
                $teamIdx = 1;
                $dateIdx+=1;
              }
            }
          }
        }
        
        $members = Array();
        $raidFightsByMember = $this->_fightManager->getAllByGuildIdGroupByPseudoIdDateTeamNumber($guildId, $raidInfo["id"]);
        
        $guildMembers = $this->_memberManager->getAllByGuildId($guildId);
        foreach ($guildMembers as $memberId => $member) {
          if (array_key_exists($memberId, $raidFightsByMember)) {
            $size = count($raidFightsByMember[$memberId]);
            $sum = array_sum(array_column($raidFightsByMember[$memberId], "sum"));
          } else {
            $size = 0;
            $sum = 0;
          }
          $members[] = Array("id" => $memberId,
                             "name" => $member->getName(),
                             "nbFights" => $size,
                             "damage" => $sum);
        }

        return $this->view->render($response, 'raid/finalise.twig',
                ['guildId' => $guildId,
                 'form' => $form,
                 'members' => $members
                ]);
    }
}