<?php

namespace App\Manager;

use Exception;

class CrewManager extends AbstractManager
{
    const DB_NAME = 'crew';
    const DB_PREFIX = 'crw';

    protected function getTable() { return [CrewManager::DB_PREFIX, CrewManager::DB_NAME]; }

    public function addCrew($memberId, $charactId, $level, $evolveld, $nbBreak, $hasWeapon, $nbWeaponBreak) {
        $this->reset();
        if (!$this->insert(Array("memberId", "charactId", "level", "evolvedGrade", "nbBreak", "hasWeapon", "nbWeaponBreak"),
                           Array($memberId, $charactId, $level, $evolveld, $nbBreak, $hasWeapon, $nbWeaponBreak))) {
            throw new Exception($this->getError());
        }
        return true;
    }

    public function updateEntity($id, array $params) /*$level, $evolveld, $nbBreak, $hasWeapon*/ {
        $this->reset();
        if(!$this->update($id, array_keys($params), array_values($params))) {
            throw new Exception($this->getError());
        }
        return true;
    }
    
    public function getAllByMemberId($id) /*$level, $evolveld, $nbBreak, $hasWeapon*/ {
        $this->reset();
        $this->addColumns(Array("id", "memberId", "charactId", "level", "nbBreak"))
             ->addWhere("memberId", strval($id), "=")
             ->addOrderBy("charactId", true);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $entities[(int) $line[$c[2]]] = Array(
                        "id" => $line[$c[0]],
                        "memberId" => $line[$c[1]],
                        "charactId" => $line[$c[2]],
                        "level" => $line[$c[3]],
                        "nbBreak" => $line[$c[4]],
                        );
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
    
}