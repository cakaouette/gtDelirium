<?php

namespace App\Manager;

use Exception;
use App\Entity\Raid;
use App\Entity\RaidDate;

class RaidManager extends AbstractManager
{
    const DB_NAME = 'raid';
    const DB_PREFIX = 'rad';
    
    protected function getTable() { return [RaidManager::DB_PREFIX, RaidManager::DB_NAME]; }

    public function getLastByDate($baseDate = NULL) {
        $d = is_null($baseDate) ? date("Y-m-d") : $baseDate;
        $this->reset();
        $this->addColumns(Array("id", "date", "duration"))
             ->addWhere("date", $d, "<=")
             ->addOrderBy("date", false)
             ->addLimit(1);
        if($this->select()) {
            $c = $this->getColumns();
            $results = $this->getResult();
            if (empty($results)) {
                throw new Exception("Aucun raid trouvé");
            }
            $line = $results[0];
            return new RaidDate($line[$c[0]], $line[$c[1]], $line[$c[2]]);
        } else {
            throw new Exception($this->getError());
        }
    }

    public function getPreviewDate() {
        $d = date("Y-m-d");
        $this->reset();
        $this->addColumns(Array("id", "date", "duration"))
             ->addWhere("date", $d, ">")
             ->addOrderBy("date", false)
             ->addLimit(1);
        if($this->select()) {
            $c = $this->getColumns();
            $results = $this->getResult();
            if (empty($results)) {
                return NULL;
            }
            $line = $results[0];
            return new RaidDate($line[$c[0]], $line[$c[1]], $line[$c[2]]);
        } else {
            throw new Exception($this->getError());
        }
    }

    /**
     * @throws Exception
     */
    public function getDates() {
        $this->reset();
        $this->addColumns(Array("id", "date", "duration"))
             ->addOrderBy("date", false);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $entities[(int) $line[$c[0]]] = new RaidDate(
                        $line[$c[0]],
                        $line[$c[1]],
                        $line[$c[2]],
                        );
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function getDateById($id) {
        $this->reset();
        $this->addColumns(Array("id", "date", "duration"))
             ->addWhere("id", strval($id), "=");
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            if (empty($results)) {
                throw new Exception("Aucun raid trouvé");
            }
            $line = $results[0];
            return new RaidDate($line[$c[0]], $line[$c[1]], $line[$c[2]]);
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function getById($id) {
        $this->reset();
        $str = "SELECT raid.id as rId, raid.date as rDate, raid.duration as rDuration,
            b1.id as b1Id, b1.name as b1Name, b1.shortName as b1Short, e1.id as e1Id, e1.name as e1Name,
            b2.id as b2Id, b2.name as b2Name, b2.shortName as b2Short, e2.id as e2Id, e2.name as e2Name,
            b3.id as b3Id, b3.name as b3Name, b3.shortName as b3Short, e3.id as e3Id, e3.name as e3Name,
            b4.id as b4Id, b4.name as b4Name, b4.shortName as b4Short, e4.id as e4Id, e4.name as e4Name
    FROM raid 
    LEFT JOIN (SELECT id, name, shortName FROM boss) b1 on raid.boss1Id = b1.id
    LEFT JOIN (SELECT id, name, shortName FROM boss) b2 on raid.boss2Id = b2.id
    LEFT JOIN (SELECT id, name, shortName FROM boss) b3 on raid.boss3Id = b3.id
    LEFT JOIN (SELECT id, name, shortName FROM boss) b4 on raid.boss4Id = b4.id
    LEFT JOIN (SELECT id, name FROM element) e1 on raid.element1Id = e1.id
    LEFT JOIN (SELECT id, name FROM element) e2 on raid.element2Id = e2.id
    LEFT JOIN (SELECT id, name FROM element) e3 on raid.element3Id = e3.id
    LEFT JOIN (SELECT id, name FROM element) e4 on raid.element4Id = e4.id
    WHERE raid.id = '$id';";
        if($this->excuseCustomQuery($str)) {
            $results = $this->getResult();
            if (empty($results)) {
                throw new Exception("Aucune guilde trouvée");
            }
            $line = $results[0];
            return new Raid(
                $line["rId"], $line["rDate"], $line["rDuration"],
                $line["b1Id"], $line["b1Name"], $line["b1Short"], $line["e1Id"], $line["e1Name"],
                $line["b2Id"], $line["b2Name"], $line["b2Short"], $line["e2Id"], $line["e2Name"],
                $line["b3Id"], $line["b3Name"], $line["b3Short"], $line["e3Id"], $line["e3Name"],
                $line["b4Id"], $line["b4Name"], $line["b4Short"], $line["e4Id"], $line["e4Name"]
                );
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function getAllWithNames() {
        $this->reset();
        $str = "SELECT raid.id as rId, raid.date as rDate, raid.duration as rDuration,
            b1.id as b1Id, b1.name as b1Name, b1.shortName as b1Short, e1.id as e1Id, e1.name as e1Name,
            b2.id as b2Id, b2.name as b2Name, b2.shortName as b2Short, e2.id as e2Id, e2.name as e2Name,
            b3.id as b3Id, b3.name as b3Name, b3.shortName as b3Short, e3.id as e3Id, e3.name as e3Name,
            b4.id as b4Id, b4.name as b4Name, b4.shortName as b4Short, e4.id as e4Id, e4.name as e4Name
    FROM raid 
    LEFT JOIN (SELECT id, name, shortName FROM boss) b1 on raid.boss1Id = b1.id
    LEFT JOIN (SELECT id, name, shortName FROM boss) b2 on raid.boss2Id = b2.id
    LEFT JOIN (SELECT id, name, shortName FROM boss) b3 on raid.boss3Id = b3.id
    LEFT JOIN (SELECT id, name, shortName FROM boss) b4 on raid.boss4Id = b4.id
    LEFT JOIN (SELECT id, name FROM element) e1 on raid.element1Id = e1.id
    LEFT JOIN (SELECT id, name FROM element) e2 on raid.element2Id = e2.id
    LEFT JOIN (SELECT id, name FROM element) e3 on raid.element3Id = e3.id
    LEFT JOIN (SELECT id, name FROM element) e4 on raid.element4Id = e4.id
    ORDER BY raid.id;";
        if($this->excuseCustomQuery($str)) {
            $entities = array();
            $results = $this->getResult();
            if (empty($results)) {
                throw new Exception("Aucune guilde trouvée");
            }
            foreach ($results as $line) {
                $entities[(int) $line["rId"]] = new Raid(
                        $line["rId"], $line["rDate"], $line["rDuration"],
                        $line["b1Id"], $line["b1Name"], $line["b1Short"], $line["e1Id"], $line["e1Name"],
                        $line["b2Id"], $line["b2Name"], $line["b2Short"], $line["e2Id"], $line["e2Name"],
                        $line["b3Id"], $line["b3Name"], $line["b3Short"], $line["e3Id"], $line["e3Name"],
                        $line["b4Id"], $line["b4Name"], $line["b4Short"], $line["e4Id"], $line["e4Name"]
                        );
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function addEntity($date, $duration, $b1Id, $e1Id, $b2Id, $e2Id,
                              $b3Id, $e3Id, $b4Id, $e4Id) {
        $this->reset();
        if (!$this->insert(Array("date", "duration", "boss1Id", "element1Id", "boss2Id", "element2Id", 
                                 "boss3Id", "element3Id", "boss4Id", "element4Id"),
                           Array($date, $duration, $b1Id, $e1Id, $b2Id, $e2Id,
                                 $b3Id, $e3Id, $b4Id, $e4Id))) {
            throw new Exception($this->getError());
        }
        return true;
    }

    public function updateEntity($id, $date, $duration, $b1Id, $e1Id, $b2Id, $e2Id,
                                 $b3Id, $e3Id, $b4Id, $e4Id) {
        $this->reset();
        $params = Array("id" => $id,
                        "date" => $date,
                        "duration" => $duration,
                        "boss1Id" => $b1Id,
                        "element1Id" => $e1Id,
                        "boss2Id" => $b2Id,
                        "element2Id" => $e2Id,
                        "boss3Id" => $b3Id,
                        "element3Id" => $e3Id,
                        "boss4Id" => $b4Id,
                        "element4Id" => $e4Id);
        if(!$this->update($id, array_keys($params), array_values($params))) {
            throw new Exception($this->getError());
        }
        return true;
    }
    
    public function deleteEntity($id) {
        $this->reset();
        $this->addWhere("id", strval($id), "=");
        if (!$this->delete()) {
            throw new Exception($this->getError());
        }
        return true;
    }
}