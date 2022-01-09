<?php
include_once("AbstractManager.php");

class AilmentEnduranceManager extends AbstractManager
{
    const DB_NAME = 'ailmentendurance';
    const DB_PREFIX = 'ate';

    public function __construct() {
        parent::__construct(AilmentEnduranceManager::DB_NAME, AilmentEnduranceManager::DB_PREFIX);
    }
    
    public function add($weaponId, $bossId, $rate): bool {
        $this->reset();
        if (!$this->insert(Array("weaponId", "bossId", "rate"), Array($weaponId, $bossId, $rate))) {
            throw new Exception($this->getError());
        }
        return true;
    }
    
    public function updateAe($id, array $params) /*$weaponId, $bossId, $rate)*/ {
        $this->reset();
        if(!$this->update($id, array_keys($params), array_values($params))) {
            throw new Exception($this->getError());
        }
        return true;
    }

}