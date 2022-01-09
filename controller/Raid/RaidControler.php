<?php
include_once('controller/AbstractControler.php');

include_once('model/Manager/RaidManager.php');
include_once('model/Manager/FightManager.php');
include_once('model/Manager/BossManager.php');
include_once('model/Manager/GuildManager.php');
include_once('model/Manager/CharacterManager.php');
include_once('model/Manager/TeamManager.php');
include_once('model/Manager/BossManager.php');
include_once('model/Manager/MemberManager.php');
include_once('model/Manager/AilmentManager.php');
include_once('model/Manager/WeaponManager.php');

class RaidControler extends AbstractControler
{
    private RaidManager $_raidManager;
    private FightManager $_fightManager;

    public function __construct() {
        parent::__construct("Raid");
        $this->_raidManager = new RaidManager();
        $this->_fightManager = new FightManager();
    }
    
    public static function setGuildRaidInfo($guildId, $raidId) {
        $raidManager = new RaidManager($guildId);
        if (!is_null($guildId)) {
            $guild = (new GuildManager())->getById($guildId);
            $_SESSION["guild"] = Array("id" => $guild->getId(), "name" => $guild->getName(), "color" => $guild->getColor());
        }
        $now = time();
        if (is_null($raidId)) {
            $raid = $raidManager->getLastByDate();
            $raidId = $raid->getId();
            $raidDate = $raid->getDate();
        } else {
            $raidDate = $raidManager->getDateById($raidId)->getDate();
        }
        $dPreview = $raidManager->getPreviewDate();
        if (!is_null($dPreview)) {
          $_SESSION["raidPreview"]["id"] = $dPreview->getId();
          $_SESSION["raidPreview"]["dateRaid"] = $dPreview->getDate();
        } else {
          $_SESSION["raidPreview"]["id"] = NULL;
          $_SESSION["raidPreview"]["dateRaid"] = NULL;
        }
        $date2 = strtotime($raidDate);
        $diff = max(($now - $date2), 0);
        $dayNumber = floor((($diff / 60) / 60 ) / 24);
        $_SESSION["raidInfo"]["id"] = $raidId;
        $_SESSION["raidInfo"]["dateRaid"] = $raidDate;
        $_SESSION["raidInfo"]["dateNumber"] = min($dayNumber, 13);
        $_SESSION["raidInfo"]["isFinished"] = $dayNumber > 13;
    }

    public function checkDate($date) {
        if ($date > date("Y-m-d")) {
            $this->addMsg("warning", "Vous ne pouvez pas anticiper une attaque");
            return false;
        }
        return true;
    }
    
    public function checkFight($bossId, $damage) {
        if(!is_numeric($damage) or is_null($damage) or (intval($damage) < 0)) {
            $this->addMsg("warning", "Les dégâts doivent être positif");
            return false;
        }
        return true;
    }

    public function addFight($guildId, $memberId, $date) {
        if (is_null($guildId)) {
            header("?page=raid&subpage=info");
        }
        $f_date = $date;
        $memberManager = new MemberManager();
        try {
            $v_members = $memberManager->getAllByGuildId($guildId);
        } catch (Exception $e) {
            $v_members = [];
            $this->addMsg("danger", $e->getMessage());
        }
        $f_memberId = $memberId;
        $f_team1 = NULL;
        $f_team2 = NULL;
        $f_team3 = NULL;
        if (!is_null($memberId)) {
            try {
                $dateRaid = $this->_raidManager->getLastByDate()->getDate();
            } catch (Exception $e) {
                $this->addMsg("danger", $e->getMessage());
            }
            $bossManager = new BossManager();
            $f_raid = $this->_raidManager->getById($_SESSION["raidInfo"]["id"]);
            $v_characters = (new CharacterManager())->getAllOrderByGradeElementName();
            $v_elements = ElementManager::getAllInRawData();
            try {
                $fights = $this->_fightManager->getAllByMemberDate($memberId, $date);
            } catch (Exception $e) {
                $this->addMsg("danger", $e->getMessage());
            }
            $teamManager = new TeamManager();
            $teamsMember = $teamManager->getAllByMember($memberId);
            foreach ($fights as $teamNumber => $fight) {
                if (!isset($fight)) {
                    $teams[$teamNumber] = new Fight("",
                        $memberId,
                        $guildId,
                        $_SESSION["raidInfo"]["id"],
                        $date,
                        $teamNumber,
                        0,
                        0,
                        (is_null($teamsMember[$teamNumber])) ? 0 : $teamsMember[$teamNumber]->getHero1Id(),
                        (is_null($teamsMember[$teamNumber])) ? 0 : $teamsMember[$teamNumber]->getHero2Id(),
                        (is_null($teamsMember[$teamNumber])) ? 0 : $teamsMember[$teamNumber]->getHero3Id(),
                        (is_null($teamsMember[$teamNumber])) ? 0 : $teamsMember[$teamNumber]->getHero4Id()
                    );
                } else {
                    $teams[$teamNumber] = $fight;
                }
            }
            $f_team1 = $teams[1];
            $f_team2 = $teams[2];
            $f_team3 = $teams[3];
        }
        $v_content_header = "Enregistrement des attaques";
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/raidPage/RaidFightView.php');
    }

    function submitFight($recorderId, $guildId, $memberId, $raidId, $date, $teamNumber, $bossId, $damage, $hero1Id, $hero2Id, $hero3Id, $hero4Id) {
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

    function saveFight($recorderId, $id, $guildId, $memberId, $raidId, $date, $teamNumber, $bossId, $damage, $hero1Id, $hero2Id, $hero3Id, $hero4Id, $deleted) {
        if ($this->_fightManager->checkFight($id, $guildId, $memberId, $date, $teamNumber)) {
            if($this->_fightManager->updateFight($id, $bossId, $damage, $hero1Id, $hero2Id, $hero3Id, $hero4Id, $recorderId, $deleted)){
                    $this->addMsg("success", "Attaque modifiée");
            }
        }
    }

    function listFight() {
        try {
            $fights = $this->_fightManager->getAll();
        } catch (Exception $e) {
            $this->addMsg("danger", $e->getMessage());
        }
        $v_content_header = "Liste des 180 dernières attaques";
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/raidPage/RaidListView.php');
    }

    function meteoFight($guildId) {
        $dateRaid = $_SESSION["raidInfo"]["dateRaid"];
        $plusDays = $_SESSION["raidInfo"]["dateNumber"] + ($_SESSION["raidInfo"]["isFinished"] ? 0 : -1);
        $date = date("Y-m-d", strtotime("$dateRaid +".max($plusDays,0)." day"));
        $v_meteo = Array();
        if ($plusDays >= 0) {
            try {
                $counts = $this->_fightManager->getCountByGuildIdDate($guildId, $date);
            } catch (Exception $e) {
                $this->addMsg("danger", $e->getMessage());
            }
    //        $_guildId = $guildId;
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
        $v_content_header = "Météo du ".$date;
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/raidPage/RaidEntriesView.php');
    }

    function followupFight($guildId) {
        if (is_null($_SESSION["raidInfo"]["dateRaid"])) {
            try {
                $dateRaid = $this->_raidManager->getLastByDate()->getDate();
            } catch (Exception $e) {
                $this->addMsg("danger", $e->getMessage());
            }
            $yesterday = date("Y-m-d", strtotime(date("Y-m-d")." -1 day"));
            $dayNumber = (DateTime::createFromFormat("Y-m-d", $yesterday)->diff(DateTime::createFromFormat("Y-m-d", $dateRaid)))->d;
        } else {
            $dateRaid = $_SESSION["raidInfo"]["dateRaid"];
            $dayNumber = $_SESSION["raidInfo"]["dateNumber"];
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
            $prevDay = $_SESSION["raidInfo"]["isFinished"] ? 13 : max($dayNumber -1, 0);
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
//                    $endFor = array_key_exists($d, $fightsByMemberByDay[$memberId]) ? $dayNumber : $prevDay;
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
        $v_content_header = "Suivi du raid, score actuel= ".number_format($v_globalSum, 0, ',', ' ');
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/raidPage/RaidFollowUpView.php');
    }

    function missedFight($guildId) {
        if (is_null($_SESSION["raidInfo"]["dateRaid"])) {
            try {
                $dateRaid = $this->_raidManager->getLastByDate()->getDate();
            } catch (Exception $e) {
                $this->addMsg("danger", $e->getMessage());
            }
            $yesterday = date("Y-m-d", strtotime(date("Y-m-d")." -1 day"));
            $dayNumber = (DateTime::createFromFormat("Y-m-d", $yesterday)->diff(DateTime::createFromFormat("Y-m-d", $dateRaid)))->d;
        } else {
            $dateRaid = $_SESSION["raidInfo"]["dateRaid"];
            $dayNumber = $_SESSION["raidInfo"]["dateNumber"] + ($_SESSION["raidInfo"]["isFinished"] ? 0 : -1);
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
        $v_content_header = "Les absences";
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/raidPage/RaidMissedView.php');
    }
    
    public function info($guildId, $raidId, $raidPreviewId) {
        $bossManager = new BossManager();
        $guildManager = new GuildManager();

        try {
            RaidControler::setGuildRaidInfo($guildId, $raidId);
        } catch (Exception $e) {
            $this->addMsg("danger", $e->getMessage());
        }
        
        $v_boss = [];
        $f_raidId = $raidPreviewId ?? $raidId;
        try {
            $v_raidInfo = $this->_raidManager->getById($f_raidId);
        } catch (Exception $e) {
            $this->addMsg("danger", $e->getMessage());
        }

        $f_guildId = $guildId;
        try {
            $v_guilds = $guildManager->getAll();
        } catch (Exception $e) {
            $v_guilds = [];
            $this->addMsg("danger", $e->getMessage());
        }
        
        try {
            $v_raids = $this->_raidManager->getDates();
        } catch (Exception $e) {
            $v_raids = [];
            $this->addMsg("danger", $e->getMessage());
        }
        
        $b1 = $v_raidInfo->getBoss1Info()["id"];
        $b2 = $v_raidInfo->getBoss2Info()["id"];
        $b3 = $v_raidInfo->getBoss3Info()["id"];
        $b4 = $v_raidInfo->getBoss4Info()["id"];
        try {
            $_ailments = (new AilmentManager)->getAll();
            $v_ailments = Array();
            foreach ($_ailments as $id => $ailment) {
              $v_ailments[$id] = ["ailment" => $ailment,
                  "weapons" => (new WeaponManager())->getByAilmentBossRaid($id, $b1, $b2, $b3, $b4)
                  ];
            }
        } catch (Exception $e) {
            $this->addMsg("warning", $e->getMessage());
            $v_boss = NULL;
            $v_ailments = Array();
        }
        
        $v_content_header = "Informations raid";
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require("view/raidPage/RaidInfoView.php");
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
    
    public function rank($id) {
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
              $countFights += count($keptDamages);
              $sumFights += array_sum($keptDamages);
            }
            $v_fights[$pseudoId]["totalCount"] = $countFights;
            $v_fights[$pseudoId]["totalSum"] = $sumFights;
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
      
        $v_content_header = "Classement dans l'alliance";
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require("view/raidPage/RaidRankView.php");
    }
    
    public function printGuildFight($guildId, $date, $filter, $hasError) {
        $v_characters = (new CharacterManager())->getAllOrderByGradeElementName();
        $v_elements = ElementManager::getAllInRawData();
        $f_date = $date;
        $f_raid = $this->_raidManager->getById($_SESSION["raidInfo"]["id"]);
        if ($hasError) {
          $this->addMsg("warning", "Il y a eu un problème pendant le traitement de votre requête");
        }

        $members = (new MemberManager)->getAllByGuildId($guildId, $filter);
        $teams = (new MemberManager())->getTeamsByGuild($guildId, $filter);
        $fights = $this->_fightManager->getAllByGuildIdDate($guildId, $date, $filter);

        $v_fights = [];
        foreach($members as $memId => $member) {
          $v_fights[$memId] = ["member" => $member->getName(), "guildId" => $guildId,
              "savedTeams" => [], "fights" => []];
          if (!array_key_exists($memId, $teams)) {
            $v_fights[$memId]["teamIds"] = '""';
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
              $v_fights[$memId]["fights"][$fightNb] = $fight;
            }
          }
        }
           
        $v_content_header = "Enregistrement des attaques";
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/raidPage/RaidFightGuildView.php');
    }
}
