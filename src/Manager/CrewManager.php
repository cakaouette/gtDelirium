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

    public function updateCrew($id, array $params) /*$level, $evolveld, $nbBreak, $hasWeapon*/ {
        $this->reset();
        if(!$this->update($id, array_keys($params), array_values($params))) {
            throw new Exception($this->getError());
        }
        return true;
    }
    
}