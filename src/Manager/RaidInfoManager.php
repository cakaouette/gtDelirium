<?php

namespace App\Manager;

//use Exceptiond;
use App\Entity\RaidInfo;

class RaidInfoManager extends AbstractManager
{
    const DB_NAME = 'raidInfo';
    const DB_PREFIX = 'rdi';

    protected function getTable() { return [RaidInfoManager::DB_PREFIX, RaidInfoManager::DB_NAME]; }
    
    public function getAllByRaid($raidId) : array {
        $this->reset();
        $this->addColumns(Array("id", "raidId", "infoId", "type", "extension", "source"))
             ->addOrderBy("id", true)
             ->addWhere("raidId", strval($raidId), "=");
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $entities[(int) $line[$c[2]]] = 
                        new RaidInfo($line[$c[0]],
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
    
    public function addEntity($raidId, $infoId, $type, $extension, $source) {
        $this->reset();
        if (!$this->insert(Array("raidId", "infoId", "type", "extension", "source"),
                           Array($raidId, $infoId, $type, $extension, $source))) {
            return false;
        }
        return true;
    }
}