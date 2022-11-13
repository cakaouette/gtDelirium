<?php

namespace App\Manager;

//use Exceptiond;
use App\Entity\BossInfo;

class BossInfoManager extends AbstractManager
{
    const DB_NAME = 'bossInfo';
    const DB_PREFIX = 'bsi';

    protected function getTable() { return [BossInfoManager::DB_PREFIX, BossInfoManager::DB_NAME]; }
    
    public function getAllByBoss($bossId) : array {
        $this->reset();
        $this->addColumns(Array("id", "bossId", "infoId", "type", "extension", "source"))
             ->addOrderBy("id", true)
             ->addWhere("bossId", strval($bossId), "=");
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $entities[(int) $line[$c[2]]] = 
                        new BossInfo($line[$c[0]],
                                      $line[$c[1]],
                                      $line[$c[2]],
                                      $line[$c[3]],
                                      $line[$c[4]],
                                      $line[$c[5]]);
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function addEntity($bossId, $infoId, $type, $extension, $source) {
        $this->reset();
        if (!$this->insert(Array("bossId", "infoId", "type", "extension", "source"),
                           Array($bossId, $infoId, $type, $extension, $source))) {
            return false;
        }
        return true;
    }
}