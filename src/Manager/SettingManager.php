<?php

namespace App\Manager;

use Exception;
use App\Entity\Setting;

class SettingManager extends AbstractManager
{
    const DB_NAME = 'setting';
    const DB_PREFIX = 'stg';
    
    private Setting $_entity;

    protected function getTable() { return [SettingManager::DB_PREFIX, SettingManager::DB_NAME]; }
    
    public function getByMemberId($memberId) {
        $entityFounded = $this->getEntityByMemberId($memberId);
        if (!$entityFounded) {
          $this->addEntity($memberId, 30);
          $this->getEntityByMemberId($memberId);
        }
        return $this->_entity;
    }
    
    private function getEntityByMemberId($memberId) {
        $this->reset();
        $this->addColumns(Array("id", "memberId", "maxLevel"))
             ->addWhere("memberId", strval($memberId), "=")
             ->addLimit(1);
        if($this->select()) {
            $c = $this->getColumns();
            $results = $this->getResult();
            if (empty($results)) {
                return false;
            }
            $line = $results[0];
            $this->_entity = new Setting($line[$c[0]], $line[$c[1]], $line[$c[2]]);
            return true;
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function addEntity($memberId, $maxLevel) {
        $this->reset();
        if (!$this->insert(Array("memberId", "maxLevel"),
                           Array($memberId, $maxLevel))) {
            throw new Exception($this->getError());
        }
        return true;
    }

    public function updateEntity($id, array $params) /*$memberId, $maxLevel*/ {
        $this->reset();
        if(!$this->update($id, array_keys($params), array_values($params))) {
            throw new Exception($this->getError());
        }
        return true;
    }
}