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
require_once('model/Manager/MemberManager.php');
use MemberManager;
require_once('model/Manager/TeamManager.php');
use TeamManager;
require_once('model/Manager/CharacterManager.php');
use CharacterManager;

final class RaidController extends BaseController
{
    private RaidManager $_raidManager;
    private FightManager $_fightManager;
    private TeamManager $_teamManager;
    
    protected function __init() {
        //TODO inject instead
        $this->_raidManager = new RaidManager();
        $this->_fightManager = new FightManager();
        $this->_teamManager = new TeamManager();
    }

    public function info(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $bossManager = new BossManager();
        $guildManager = new GuildManager();

        try {
            $form = $request->getParsedBody();
            
            $formGuildId = $form['guildId'];
            if (is_null($formGuildId)) {
                $guildId = $this->session->get('guild')['id'];
            } else {
                $guildId = $formGuildId;
                $guild = $guildManager->getById($guildId);
                $this->session->set('guild', [
                    "id" => $guild->getId(),
                    "name" => $guild->getName(),
                    "color" => $guild->getColor()
                ]);
            }
            $v_guilds = $guildManager->getAll();
            
            $formRaidId = $request->getMethod() === "GET" ? $this->session->get('raidInfo')['id'] : $form['raidId'];
            if (is_null($formRaidId)) {
                $raid = $this->_raidManager->getLastByDate();
                $raidId = $raid->getId();
                $raidDate = $raid->getDate();
            } else {
                $raidId = $formRaidId;
                $raidDate = $this->_raidManager->getDateById($raidId)->getDate();
            }
            $dPreview = $this->_raidManager->getPreviewDate();
            if (!is_null($dPreview)) {
                $raidPreviewId = $dPreview->getId();
            }
            $date2 = strtotime($raidDate);
            $now = time();
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
        
        return $this->view->render($response, 'raid/info.twig',
                ['guildId' => $guildId,
                 'guilds' => $v_guilds,
                 'raidId' => $raidId,
                 'raids' => $v_raids,
                 'bosses' => $v_bosses,
                 'ailments' => $v_ailments]);
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
            $members = MemberManager::getAllByGuildIdInRawData($guildId, $date, false);
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
            } catch (Exception $e) {
                $this->addMsg("danger", $e->getMessage());
            }
            $yesterday = date("Y-m-d", strtotime(date("Y-m-d")." -1 day"));
            $dayNumber = (DateTime::createFromFormat("Y-m-d", $yesterday)->diff(DateTime::createFromFormat("Y-m-d", $dateRaid)))->d;
        } else {
            $dateRaid = $raidInfo["dateRaid"];
            $dayNumber = $raidInfo["dateNumber"];
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
            $members = MemberManager::getAllWithDateStartByGuildIdInRawData($guildId, date('Y-m-d'), false);// TODO get id, name et dateStart pour pas check la diff si arrivé en cours de raid
            $i = -1;
            $v_globalSum = 0;
            $d = date("Y-m-d", strtotime("$dateRaid +$dayNumber day"));
            $prevDay = $raidInfo["isFinished"] ? 13 : max($dayNumber -1, 0);
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
                    for (; $j < 14; $j++) {
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
                } else {
                    $v_damagesByMemberByDay[$i] = Array("memberId" => $memberId, "memberName" => $memberName,
                        "day0" => NULL, "day1" => NULL, "day2" => NULL, "day3" => NULL, "day4" => NULL,
                        "day5" => NULL, "day6" => NULL, "day7" => NULL, "day8" => NULL, "day9" => NULL,
                        "day10" => NULL, "day11" => NULL, "day12" => NULL, "day13" => NULL,
                        "daysSum" => NULL, "yesterdaySum" => 0,
                        "day0Prev" => NULL, "day1Prev" => NULL, "day2Prev" => NULL, "day3Prev" => NULL, "day4Prev" => NULL,
                        "day5Prev" => NULL, "day6Prev" => NULL, "day7Prev" => NULL, "day8Prev" => NULL, "day9Prev" => NULL,
                        "day10Prev" => NULL, "day11Prev" => NULL, "day12Prev" => NULL, "day13Prev" => NULL,
                        "daysSumPrev" => NULL, "yesterdaySumPrev" => NULL
                        );
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
            'prevDay' => $v_prevDayNumber
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
            $guildManager = new GuildManager();
            $v_guilds = $guildManager->getAll();
            $f_guildId = $guildId;

            $members = MemberManager::getAllWithDateStartByGuildIdInRawData($guildId, date('Y-m-d'), false);// TODO get id, name et dateStart pour pas check la diff si arrivé en cours de raid
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

    public function fights(ServerRequestInterface $request, ResponseInterface $response, string $guildId = null): ResponseInterface {
        if (is_null($guildId)) $guildId = $this->session->get('guild')['id'];
        $raidInfo = $this->session->get('raidInfo');
        $date = date("Y-m-d");
        $params = $request->getQueryParams();
        if (isset($params['date'])) {
            $date = $params['date'];
            if (!$this->checkDate($date)) {
                $date = date("Y-m-d");
            }
        }
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

        $members = (new MemberManager)->getAllByGuildId($guildId, $filter);
        $teams = (new MemberManager())->getTeamsByGuild($guildId, $filter);
        $fights = $this->_fightManager->getAllByGuildIdDate($guildId, $date, $filter);

        $v_fights = [];
        foreach($members as $memId => $member) {
            $v_fights[$memId] = ["member" => $member->getName(), "guildId" => $guildId, "savedTeams" => [], "fights" => []];
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
        }

        $v_characters = (new CharacterManager())->getAllOrderByGradeElementName();
        $characters = [];
        foreach ($v_characters as $character) {
            $g = $character->getGrade();
            if (!isset($characters[$g])) $characters[$g] = ['name' => $g, 'elements' => []];
            $e = $character->getElementInfo();
            if (!isset($characters[$g]['elements'][$e['id']])) $characters[$g]['elements'][$e['id']] = ['name' => $e['name'], 'characters' => []];
            $characters[$g]['elements'][$e['id']]['characters'][$character->getId()] = $character->getName();
        }

        return $this->view->render($response, 'raid/fights.twig', [
            'title' => "Enregistrement des attaques",
            'filter' => $filter,
            'members' => $v_fights,
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

    private function submitFight($recorderId, $guildId, $memberId, $raidId, $date, $teamNumber, $bossId, $damage, $hero1Id, $hero2Id, $hero3Id, $hero4Id) {
        if ($bossId == 0 or is_null($damage)) {
            $this->addMsg("warning", "Selectionner un boss et définisser des dommages");
            return;
        }
        try {
            if (!$this->_fightManager->isExist($memberId, $date, $teamNumber)) {
                try {
                    $this->_fightManager->addFight($memberId, $guildId, $raidId, $date,
                                                  $teamNumber, $bossId, $damage,
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

    private function saveFight($recorderId, $id, $guildId, $memberId, $raidId, $date, $teamNumber, $bossId, $damage, $hero1Id, $hero2Id, $hero3Id, $hero4Id, $deleted) {
        if ($this->_fightManager->checkFight($id, $guildId, $memberId, $date, $teamNumber)) {
            if($this->_fightManager->updateFight($id, $bossId, $damage, $hero1Id, $hero2Id, $hero3Id, $hero4Id, $recorderId, $deleted)){
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
}