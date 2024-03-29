<?php

namespace App\Manager;

use Exception;
use Monolog\Logger;
use Laminas\Config\Config;
use App\Entity\Fight;

class FightManager extends  AbstractManager
{
    const DB_NAME = 'fight';
    const DB_PREFIX = 'fgt';

    private CharacterManager $_characterManager;

    public function __construct(DatabaseClass $db, Logger $logger, CharacterManager $characterManager) {
        parent::__construct($db, $logger);
        $this->_characterManager = $characterManager;
    }

    protected function getTable() { return [FightManager::DB_PREFIX, FightManager::DB_NAME]; }

    public function getCountByGuildIdDate($guildId, $date) {
        $this->reset();
        $this->addColumns(Array("pseudoId"))
            ->addWhere("date", strval($date), "=")
            ->addWhere("guildId", strval($guildId), "=")
            ->addWhere("deleted", "1", "!=")
            ->addfunction("count", "teamNumber")
            ->addfunction("sum", "damage")
            ->addGroupBy("pseudoId");
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $entities[(int) $line[$c[0]]] = Array("counter" => $line[$c[1]], "damages" => $line[$c[2]]);
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }

    public function getAllByGuildIdDate($guildId, $date, $isExtra = 0, $memberFilter = NULL) {
        $this->reset();
        $this->addColumns(Array("id", "pseudoId", "guildId", "raidId", "date", "teamNumber",
                                "bossId", "damage", "hero1Id", "hero2Id", "hero3Id", "hero4Id"))
             ->addColumns(Array("name"), false, "member", "mbr")
             ->addColumns(Array("name"), false, "guild", "gld")
             ->addColumns(Array("name"), false, "boss", "bss")
             ->addJoin("LEFT", "pseudoId", "id", "member")
             ->addJoin("LEFT", "bossId", "id", "boss")
             ->addJoin("LEFT", "guildId", "id", "guild")
             ->addWhere("guildId", $guildId, "=")
             ->addWhere("date", $date, "=")
             ->addWhere("isExtraDamage", $isExtra, "=")
             ->addWhere("deleted", "1", "!=")
             ->addOrderBy("pseudoId", true)
             ->addOrderBy("teamNumber", true);
        if (!is_null($memberFilter)) {
          $this->addWhere("pseudoId", $memberFilter, "=");
        }
        if($this->select()) {
            $c = $this->getColumns();
            $results = $this->getResult();
            $characNames = $this->_characterManager->getAllInRawData();
            $ret = [];
            foreach ($results as $line) {
                $entity = new Fight($line[$c[0]], $line[$c[1]], $line[$c[2]], $line[$c[3]], $line[$c[4]], $line[$c[5]],
                    $line[$c[6]], $line[$c[7]], $line[$c[8]], $line[$c[9]], $line[$c[10]], $line[$c[11]],
                    $line[$c[14]],
                    $characNames[$line[$c[8]] ?? 0],
                    $characNames[$line[$c[9]] ?? 0],
                    $characNames[$line[$c[10]] ?? 0],
                    $characNames[$line[$c[11]] ?? 0],
                    $line[$c[12]],
                    $line[$c[13]]
                );
                if (!array_key_exists($line[$c[1]], $ret)) {
                  $ret[$line[$c[1]]] = ["fights" => [$line[$c[5]] => $entity]];
                } else {
                  $ret[$line[$c[1]]]["fights"][$line[$c[5]]] = $entity;
                }
            }
            return $ret;
        } else {
            throw new Exception($this->getError());
        }
    }

    public function getAllByGuildIdDateGroupByPseudoIdDate($guildId, $date) {
        $date1 = $date;
        $date14 = date("Y-m-d", strtotime("$date1 +13 day"));
        $this->reset();
        $this->addColumns(Array("pseudoId", "date"))
            ->addfunction("sum", "damage")
            ->addWhere("date", strval($date1), ">=")
            ->addWhere("date", strval($date14), "<=")
            ->addWhere("guildId", strval($guildId), "=")
            ->addWhere("deleted", "1", "!=")
            ->addGroupBy("pseudoId")
            ->addGroupBy("date")
            ->addOrderBy("pseudoId", true)
            ->addOrderBy("date", false);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                if (array_key_exists((int) $line[$c[0]], $entities)) {
                    $entities[(int) $line[$c[0]]][$line[$c[1]]] = $line[$c[2]];
                } else {
                    $entities[(int) $line[$c[0]]] = Array($line[$c[1]] => $line[$c[2]]);
                }
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }

    public function getAllByGuildIdDateGroupByTeamNumberDate($guildId, $date) {
        $date1 = $date;
        $date14 = date("Y-m-d", strtotime("$date1 +13 day"));
        $this->reset();
        $this->addColumns(Array("pseudoId", "date"))
             ->addfunction("count", "teamNumber")
             ->addJoin("LEFT", "pseudoId", "id", "member")
             ->addWhere("date", strval($date1), ">=")
             ->addWhere("date", strval($date14), "<=")
             ->addWhere("guildId", strval($guildId), "=")
             ->addWhere("deleted", "1", "!=")
             ->addGroupBy("pseudoId")
             ->addGroupBy("date");
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                if (array_key_exists($line[$c[0]], $entities)) {
                    $entities[$line[$c[0]]][$line[$c[1]]] = $line[$c[2]];
                } else {
                    $entities[$line[$c[0]]] = Array($line[$c[1]] => $line[$c[2]]);
                }
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }

    public function getAll() {
        $this->reset();
        $this->addColumns(Array("id", "pseudoId", "guildId", "raidId", "date", "teamNumber",
                                "bossId", "damage", "hero1Id", "hero2Id", "hero3Id", "hero4Id"))
             ->addColumns(Array("name"), false, "member", "mbr")
             ->addColumns(Array("name"), false, "guild", "gld")
             ->addColumns(Array("name"), false, "boss", "bss")
             ->addJoin("LEFT", "pseudoId", "id", "member")
             ->addJoin("LEFT", "bossId", "id", "boss")
             ->addJoin("LEFT", "guildId", "id", "guild")
             ->addWhere("deleted", "1", "!=")
             ->addOrderBy("date", false)
             ->addOrderBy("name", true, "member")
             ->addOrderBy("teamNumber", true, "fight")
             ->addLimit(180);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            $characNames = $this->_characterManager->getAllInRawData();
            foreach ($results as $line) {
                $entity = new Fight($line[$c[0]], $line[$c[1]], $line[$c[2]], $line[$c[3]], $line[$c[4]], $line[$c[5]],
                    $line[$c[6]], $line[$c[7]], $line[$c[8]], $line[$c[9]], $line[$c[10]], $line[$c[11]],
                    $line[$c[14]],
                    $characNames[$line[$c[8]] ?? 0],
                    $characNames[$line[$c[9]] ?? 0],
                    $characNames[$line[$c[10]] ?? 0],
                    $characNames[$line[$c[11]] ?? 0],
                    $line[$c[12]],
                    $line[$c[13]]
                );
                $entities[(int) $line[$c[0]]] = $entity;
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }

    public function addFight($pseudoId, $guildId, $raidId, $date,
            $teamNumber, $bossId, $damage, $isExtra,
            $hero1Id, $hero2Id, $hero3Id, $hero4Id,
            $recorderId) {
        $this->reset();
        if ( !$this->insert(Array("pseudoId", "guildId", "raidId", "date",
                            "teamNumber", "bossId", "damage", "isExtraDamage",
                            "hero1Id", "hero2Id", "hero3Id", "hero4Id",
                            "recorderId", "deleted"), 
                      Array($pseudoId, $guildId, $raidId, $date,
                            $teamNumber, $bossId, $damage, $isExtra,
                            $hero1Id, $hero2Id, $hero3Id, $hero4Id,
                            $recorderId, "0"))
            ) {
            throw new Exception($this->getError());
        }
        return true;
    }

    public function getAllByMemberDate($memberId, $date) {
        $this->reset();
        $this->addColumns(Array("id", "pseudoId", "guildId", "raidId", "date", "teamNumber",
                                "bossId", "damage", "hero1Id", "hero2Id", "hero3Id", "hero4Id"))
             ->addColumns(Array("name"), false, "member", "mbr")
             ->addColumns(Array("name"), false, "guild", "gld")
             ->addColumns(Array("name"), false, "boss", "bss")
             ->addJoin("LEFT", "pseudoId", "id", "member")
             ->addJoin("LEFT", "bossId", "id", "boss")
             ->addJoin("LEFT", "guildId", "id", "guild")
             ->addWhere("pseudoId", $memberId, "=")
             ->addWhere("date", $date, "=")
             ->addWhere("deleted", "1", "!=")
             ->addOrderBy("teamNumber", true)
             ->addLimit(3);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = array(1 => NULL, 2 => NULL, 3 => NULL);
            $results = $this->getResult();
            $characNames = $this->_characterManager->getAllInRawData();
            foreach ($results as $line) {
                $entity = new Fight($line[$c[0]], $line[$c[1]], $line[$c[2]], $line[$c[3]], $line[$c[4]], $line[$c[5]],
                    $line[$c[6]], $line[$c[7]], $line[$c[8]], $line[$c[9]], $line[$c[10]], $line[$c[11]],
                    $line[$c[14]],
                    $characNames[$line[$c[8]] ?? 0],
                    $characNames[$line[$c[9]] ?? 0],
                    $characNames[$line[$c[10]] ?? 0],
                    $characNames[$line[$c[11]] ?? 0],
                    $line[$c[12]],
                    $line[$c[13]]
                );
                $entities[(int) $line[$c[5]]] = $entity;
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function isExist($memberId, $date, $teamNumber, $isExtra): int {
        $this->reset();
        $this->addColumns(Array("teamNumber"))
             ->addWhere("date", $date, "=")
             ->addWhere("pseudoId", $memberId, "=")
             ->addWhere("teamNumber", $teamNumber, "=")
             ->addWhere("isExtraDamage", $isExtra, "=")
             ->addWhere("deleted", "1", "!=")
             ->addLimit(1);
        if($this->select()) {
            $c = $this->getColumns();
            $results = $this->getResult();
            foreach ($results as $line) {
                return true;
            }
            return false;
        } else {
            throw new Exception($this->getError());
        }
    }

    public function checkFight($id, $guildId, $memberId, $date, $teamNumber, $isExtra): bool {
        $this->reset();
        $this->addColumns(Array("teamNumber"))
             ->addWhere("id", $id, "=")
             ->addWhere("guildId", $guildId, "=")
             ->addWhere("pseudoId", $memberId, "=")
             ->addWhere("date", $date, "=")
             ->addWhere("teamNumber", $teamNumber, "=")
             ->addWhere("isExtraDamage", $isExtra, "=")
             ->addLimit(1);
        if($this->select()) {
            $c = $this->getColumns();
            if (sizeof($this->getResult()) == 1) {
                return true;
            }
            return false;
        } else {
            throw new Exception($this->getError());
        }
    }

    public function updateFight($id, $bossId, $damage, $isExtra, $hero1Id, $hero2Id, $hero3Id, $hero4Id, $recorderId, $deleted) {
        $this->reset();
        if(!$this->update($id, Array("bossId", "damage", "isExtraDamage", "hero1Id", "hero2Id", "hero3Id", "hero4Id", "recorderId", "deleted"),
                               Array($bossId, $damage, $isExtra, $hero1Id, $hero2Id, $hero3Id, $hero4Id, $recorderId, $deleted))) {
            throw new Exception($this->getError());
        }
        return true;
    }

    public function updateFightDamage($id, $damage, $recorderId) {
        $this->reset();
        if(!$this->update($id, Array("damage", "recorderId"),
                               Array($damage, $recorderId))) {
            throw new Exception($this->getError());
        }
        return true;
    }

    public function getLastByMemberDateTeam($memberId, $date, $teamNumber, $limit) {
        $date14 = date("Y-m-d", strtotime("$date +13 day"));
        $this->reset();
        $this->addColumns(Array("id", "pseudoId", "guildId", "raidId", "date", "teamNumber",
                                "bossId", "damage", "hero1Id", "hero2Id", "hero3Id", "hero4Id"))
             ->addWhere("pseudoId", strval($memberId), "=")
             ->addWhere("date", $date, ">=")
             ->addWhere("date", $date14, "<=")
             ->addWhere("teamNumber", strval($teamNumber), "=")
             ->addWhere("deleted", "1", "!=")
             ->addWhere("damage", "0", ">")
             ->addOrderBy("date", false)
             ->addLimit($limit);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            $characNames = $this->_characterManager->getAllInRawData();
            foreach ($results as $line) {
                $entity = new Fight($line[$c[0]], $line[$c[1]], $line[$c[2]], $line[$c[3]], $line[$c[4]], $line[$c[5]],
                    $line[$c[6]], $line[$c[7]], $line[$c[8]], $line[$c[9]], $line[$c[10]], $line[$c[11]],
                );
                $entities[] = $entity;
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function getAllByRaid($date) {
        $date14 = date("Y-m-d", strtotime("$date +13 day"));
        $this->reset();
        $this->addColumns(Array("id", "pseudoId", "bossId", "damage"))
             ->addColumns(Array("name"), false, MemberManager::DB_NAME, MemberManager::DB_PREFIX)
             ->addColumns(Array("name", "color"), false, GuildManager::DB_NAME, GuildManager::DB_PREFIX)
             ->addJoin("LEFT", "pseudoId", "id", MemberManager::DB_NAME)
             ->addJoin("LEFT", "guildId", "id", GuildManager::DB_NAME)
             ->addWhere("date", $date, ">=")
             ->addWhere("date", $date14, "<=")
             ->addWhere("deleted", "1", "!=")
//             ->betweenWhere("pseudoId", "1", "11")
             ->addOrderBy("pseudoId", true)
             ->addOrderBy("bossId", true)
             ->addOrderBy("date", true);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $id = $line[$c[0]];
                $pseudoId = $line[$c[1]];
                $bossId = $line[$c[2]];
                if (!array_key_exists($pseudoId, $entities)) {
                    $entities[$pseudoId] = 
                        Array("memberName" => $line[$c[4]],
                              "guildName" => $line[$c[5]],
                              "guildColor" => $line[$c[6]],
                              "bosses" => [
                                  $bossId => [
                                      "damages" => [$line[$c[3]]]
                                             ]
                                          ]
                            );
                } else {
                    if (!array_key_exists($bossId, $entities[$pseudoId]["bosses"])) {
                        $entities[$pseudoId]["bosses"][$bossId] = 
                              [
                                  "damages" => [$line[$c[3]]]
                              ];
                    } else {
                        $entities[$pseudoId]["bosses"][$bossId]["damages"][] = $line[$c[3]];
                    }
                }
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function getLastFightRecorded($guildId) {
      $this->reset();
      $str = "SELECT fight.id, member.name as pseudoName, fight.bossId,
                     fight.damage, fight.date,
                     fight.hero1Id, fight.hero2Id, fight.hero3Id, fight.hero4Id
              FROM `fight`
              LEFT JOIN member
                      ON fight.pseudoId = member.id
              WHERE fight.pseudoId != fight.recorderId 
                      AND fight.guildId = $guildId
                      AND fight.deleted != 1
              ORDER BY fight.date DESC, fight.id DESC
              LIMIT 1";
        if($this->excuseCustomQuery($str)) {
            $results = $this->getResult();
            if (empty($results)) {
                return new Fight(
                  0, 0, 0, 0, 0, 0, 0, 0,
                  0, 0, 0, 0,
                  "", "", "", "", "", "Inconnu"
                  );
//                throw new Exception("Pas de combat trouvé");
            }
            $line = $results[0];
            return new Fight(
                $line["id"], 0, 0, 0, "", 0, $line["bossId"], $line["damage"],
                $line["hero1Id"], $line["hero2Id"], $line["hero3Id"], $line["hero4Id"],
                "", "", "", "", "", $line["pseudoName"]
                );
        } else {
            throw new Exception($this->getError());
        }
    }

    public function getAllByRaidAndGuild($date, $guildId = null) {
        $date14 = date("Y-m-d", strtotime("$date +13 day"));
        $this->reset();
        $this->addColumns(Array("id", "pseudoId", "bossId", "damage", "date", "hero1Id", "hero2Id", "hero3Id", "hero4Id"))
             ->addColumns(Array("name"), false, MemberManager::DB_NAME, MemberManager::DB_PREFIX)
             ->addJoin("LEFT", "pseudoId", "id", MemberManager::DB_NAME)
             ->addJoin("LEFT", "guildId", "id", GuildManager::DB_NAME)
             ->addWhere("date", $date, ">=")
             ->addWhere("date", $date14, "<=")
             ->addWhere("deleted", "1", "!=")
//             ->betweenWhere("pseudoId", "1", "11")
             ->addOrderBy("pseudoId", true)
             ->addOrderBy("bossId", true)
             ->addOrderBy("date", true);
        if (!is_null($guildId)) $this->addWhere("guildId", $guildId, "=");
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $id = $line[$c[0]];
                $pseudoId = $line[$c[1]];
                $bossId = $line[$c[2]];
                $damage = $line[$c[3]];
                $date = $line[$c[4]];
                $hero1 = $line[$c[5]];
                $hero2 = $line[$c[6]];
                $hero3 = $line[$c[7]];
                $hero4 = $line[$c[8]];
                $member = $line[$c[9]];
                if (!isset($entities[$pseudoId])) $entities[$pseudoId] = [ 'name' => $member, 'days' => [] ];
                $entities[$pseudoId]['days'][$date][] = [
                    'boss' => $bossId,
                    'damage' => $damage,
                    'team' => [ $hero1, $hero2, $hero3, $hero4]
                ];
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function getAllByGuildIdGroupByPseudoIdDateTeamNumber($guildId, $raidId, $memberId = NULL) {
        $this->reset();
        $this->addColumns(Array("id", "pseudoId", "guildId", "raidId", "date", "teamNumber", "damage"))
            ->addfunction("sum", "damage")
            ->addWhere("guildId", strval($guildId), "=")
            ->addWhere("raidId", strval($raidId), "=")
            ->addWhere("deleted", "1", "!=")
            ->addGroupBy("pseudoId")
            ->addGroupBy("date")
            ->addGroupBy("teamNumber")
            ->addOrderBy("pseudoId", true)
            ->addOrderBy("date", true)
            ->addOrderBy("teamNumber", true);
        if (!is_null($memberId)) {
          $this->addWhere("pseudoId", strval($memberId), "=");
        }
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                if (array_key_exists((int) $line[$c[1]], $entities)) {
                    $entities[(int) $line[$c[1]]][] = 
                            Array("id" => $line[$c[0]],
                                  "date" => $line[$c[4]],
                                  "teamNumber" => $line[$c[5]],
                                  "damage" => $line[$c[6]],
                                  "sum" => $line[$c[7]]);
                } else {
                    $entities[(int) $line[$c[1]]] = Array(
                            Array("id" => $line[$c[0]],
                                  "date" => $line[$c[4]],
                                  "teamNumber" => $line[$c[5]],
                                  "damage" => $line[$c[6]],
                                  "sum" => $line[$c[7]]));
                }
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }

    public function getFightSummaryByPseudoIdGroupByRaidIdBossId($memberId) {
        $this->reset();
        $this->addColumns(Array("bossId", "raidId"))
            ->addfunction("count", "damage")
            ->addfunction("sum", "damage")
            ->addWhere("pseudoId", strval($memberId), "=")
            ->addWhere("deleted", "1", "!=")
            ->addGroupBy("raidId")
            ->addGroupBy("bossId");
        if ($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                if (array_key_exists((int) $line[$c[1]], $entities)) {
                  $entities[(int) $line[$c[1]]][(int) $line[$c[0]]] = 
                          ["count" => $line[$c[2]], "sum" => $line[$c[3]]];
                } else {
                  $entities[(int) $line[$c[1]]] = Array(
                      (int) $line[$c[0]] => ["count" => $line[$c[2]], "sum" => $line[$c[3]]]
                  );
                }
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
}