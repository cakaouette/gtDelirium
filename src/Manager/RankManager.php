<?php

namespace App\Manager;

use Exception;

class RankManager extends AbstractManager 
{
    const DB_NAME = 'rank';
    const DB_PREFIX = 'rnk';

    protected function getTable() { return [RankManager::DB_PREFIX, RankManager::DB_NAME]; }
    
    public function getAll() {
        $this->reset();
        $this->addColumns(Array("guildId", "raidId", "rank", "damage"))
             ->addColumns(Array("date"), false, RaidManager::DB_NAME, RaidManager::DB_PREFIX)
             ->addJoin("LEFT", "raidId", "id", RaidManager::DB_NAME)
             ->addOrderBy("guildId", true)
             ->addOrderBy("raidId", true);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            if (empty($results)) {
                throw new Exception("Aucune score de raid trouvé");
            }
            foreach ($results as $line) {
              $info = Array("rank" => $line[$c[2]],
                              "damage" => $line[$c[3]],
                              "timestamp" => strtotime($line[$c[4]])
                        );
              if (array_key_exists($line[$c[0]], $entities)) {
                $entities[(int) $line[$c[0]]][(int) $line[$c[1]]] = $info;
              } else {
                $entities[(int) $line[$c[0]]] = [(int) $line[$c[1]] =>$info];
              }
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
}