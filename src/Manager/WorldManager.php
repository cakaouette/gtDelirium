<?php

namespace App\Manager;

use Exception;
use App\Entity\World;

class WorldManager extends AbstractManager
{
    const DB_NAME = 'world';
    const DB_PREFIX = 'wld';
    
    protected function getTable() { return [WorldManager::DB_PREFIX, WorldManager::DB_NAME]; }
    
    public function getAll() {
        $this->reset();
        $this->addColumns(Array("id", "number", "maxLevel", "disable"))
             ->addOrderBy("number", false);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $entities[(int) $line[$c[0]]] = new World(
                        $line[$c[0]],
                        $line[$c[1]],
                        $line[$c[2]],
                        $line[$c[3]],
                        );
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function addEntity($worldNumber, $maxLevel, $isDisabled) {
        $this->reset();
        if (!$this->insert(Array("number", "maxLevel", "disable"),
                           Array($worldNumber, $maxLevel, $isDisabled))) {
            throw new Exception($this->getError());
        }
        return true;
    }

    public function updateEntity($id, $worldNumber, $maxLevel, $isDisabled) {
        $this->reset();
        $params = Array("number" => $worldNumber,
                        "maxLevel" => $maxLevel,
                        "disable" => $isDisabled);
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