<?php

namespace App\Manager;

use Exception;
use App\Entity\Ailment;

class AilmentManager extends AbstractManager
{
    const DB_NAME = 'ailment';
    const DB_PREFIX = 'ait';

    protected function getTable() { return [AilmentManager::DB_PREFIX, AilmentManager::DB_NAME]; }
    
    public function getAll() {
        $this->reset();
        $this->addColumns(Array("id", "name"))
             ->addOrderBy("id", true);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $entities[(int) $line[$c[0]]] = new Ailment(
                        $line[$c[0]],
                        $line[$c[1]],
                        );
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
}