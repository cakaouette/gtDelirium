<?php

namespace App\Manager;

//use Exceptiond;
use App\Entity\BossStrategy;

class BossStrategyManager extends AbstractManager
{
    const DB_NAME = 'bossStrategy';
    const DB_PREFIX = 'bss';

    protected function getTable() { return [BossStrategyManager::DB_PREFIX, BossStrategyManager::DB_NAME]; }
    
    public function getBossStrategyById($bossId) : BossStrategy {
        $this->reset();
        $this->addColumns(Array("id", "bossId", "strategy"))
             ->addWhere("bossId", strval($bossId), "=")
             ->addLimit(1);
        if($this->select()) {
            $c = $this->getColumns();
            $results = $this->getResult();
            if (sizeof($results) == 1) {
                $line = $results[0];
                return new BossStrategy($line[$c[0]], $line[$c[1]], $line[$c[2]]);
            } else {
                return new BossStrategy(0, 0, "");
            }
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function addEntity($bossId, $strategy) {
        $this->reset();
        $this->setDelimiter("\"");
        if (!$this->insert(Array("bossId", "strategy"),
                           Array($bossId, $strategy))) {
            return false;
            throw new Exception($this->getError());
        }
        return true;
    }
    
    public function updateEntity($id, $strategy) {
        $this->reset();
        $this->setDelimiter("\"");
        if(!$this->update($id, Array("strategy"),
                               Array($strategy))) {
            throw new Exception($this->getError());
        }
        return true;
    }
}