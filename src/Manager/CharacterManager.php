<?php

namespace App\Manager;

use Exception;
use App\Entity\Crew;
use App\Entity\Character;

class CharacterManager extends AbstractManager
{
    const DB_NAME = 'charact';
    const DB_PREFIX = 'chr';

    protected function getTable() { return [CharacterManager::DB_PREFIX, CharacterManager::DB_NAME]; }

    public function getAllOrderByGradeElementName() {
        $this->reset();
        $this->addColumns(Array("id", "name", "elementId", "grade"))
             ->addColumns(Array("name"), false, ElementManager::DB_NAME, ElementManager::DB_PREFIX)
             ->addJoin("LEFT", "elementId", "id", ElementManager::DB_NAME)
             ->addOrderBy("grade", false)
             ->addOrderBy("elementId", true)
             ->addOrderBy("name", true);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $entity = new Character($line[$c[0]],
                        $line[$c[1]],
                        $line[$c[2]],
                        $line[$c[3]],
                        $line[$c[4]]
                        );
                $entities[(int) $line[$c[0]]] = $entity;
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function getAllInRawData(): Array {
        $db = $this->_db;
        $stmt = $db->query('SELECT * FROM charact');

        $entities = array("0" => "");
        while ($entity = $stmt->fetch()) {
            $entities[$entity["id"]] = $entity["name"];
        }
        return $entities;
    }
    
    public function getAllForMember($memberId) {
        $this->reset();
        $str = "SELECT charact.id as chr_id, charact.name as chr_name, charact.elementId as chr_elementId, charact.grade as chr_grade, crw_level, crw_evolvedGrade, crw_nbBreak, crw_hasWeapon, crw_nbWeaponBreak, crw_id
FROM charact
LEFT JOIN (
    SELECT crew.charactId as crw_charactId, crew.level as crw_level, crew.evolvedGrade as crw_evolvedGrade, crew.nbBreak as crw_nbBreak, crew.hasWeapon as crw_hasWeapon, crew.nbWeaponBreak as crw_nbWeaponBreak, crew.id as crw_id
    FROM crew
    WHERE crew.memberId = '$memberId' ) crew
ON charact.id = crw_charactId
ORDER BY charact.grade DESC, charact.elementId ASC, charact.name ASC;";
        if($this->excuseCustomQuery($str)) {
            $results = $this->getResult();
            foreach ($results as $line) {
                $entity = new Crew($line["chr_id"],
                        $line["chr_name"],
                        $line["chr_elementId"],
                        $line["chr_grade"],
                        $line["crw_level"],
                        $line["crw_evolvedGrade"],
                        $line["crw_nbBreak"],
                        (int) $line["crw_hasWeapon"],
                        $line["crw_nbWeaponBreak"],
                        $line["crw_id"]
                        );
                $entities[(int) $line["chr_id"]] = $entity;
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }

    public function addHero($name, $grade, $elementId) {
        $this->reset();
        if (!$this->insert(Array("name", "grade", "elementId", "beginAilmentId", "endAilmentId"),
                           Array($name, $grade, $elementId, 0, 0))
        ) {
            throw new Exception($this->getError());
        }
        return true;
    }

    public function updateFight($id, $bossId, $damage, $hero1Id, $hero2Id, $hero3Id, $hero4Id, $recorderId, $deleted) {
        $this->reset();
        if(!$this->update($id, Array("bossId", "damage" , "hero1Id", "hero2Id", "hero3Id", "hero4Id", "recorderId", "deleted"),
                               Array($bossId, $damage, $hero1Id, $hero2Id, $hero3Id, $hero4Id, $recorderId, $deleted))) {
            throw new Exception($this->getError());
        }
        return true;
    }

    public function updateHero($id, $name, $grade, $elementId) {
        $this->reset();
        if(!$this->update($id, Array("name", "grade" , "elementId"),
                               Array($name, $grade, $elementId))) {
            throw new Exception($this->getError());
        }
        return true;
    }

    public function deleteCharact($id) {
        $this->reset();
        $this->addWhere("id", strval($id), "=");
        if (!$this->delete()) {
            throw new Exception($this->getError());
        }
        return true;
    }

}