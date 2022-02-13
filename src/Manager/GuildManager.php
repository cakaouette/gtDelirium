<?php

namespace App\Manager;

use Exception;
use App\Entity\Guild;

class GuildManager extends AbstractManager 
{
    const DB_NAME = 'guild';
    const DB_PREFIX = 'gld';

    protected function getTable() { return [GuildManager::DB_PREFIX, GuildManager::DB_NAME]; }

    /**
     * @throws Exception
     */
    public function getAll() {
        $this->reset();
        $this->addColumns(Array("id", "name", "color"))
             ->addOrderBy("id", true);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            if (empty($results)) {
                throw new Exception("Aucune guilde trouvée");
            }
            foreach ($results as $line) {
                $entities[(int) $line[$c[0]]] = new Guild(
                        $line[$c[0]],
                        $line[$c[1]],
                        $line[$c[2]],
                        );
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }

    public function getById($id) {
        $this->reset();
        $this->addColumns(Array("id", "name", "color",))
             ->addWhere("id", strval($id), "=");
        if($this->select()) {
            $c = $this->getColumns();
            $results = $this->getResult();
            if (empty($results)) {
                throw new Exception("Aucune guilde trouvée");
            }
            $line = $results[0];
            return new Guild(
                $line[$c[0]],
                $line[$c[1]],
                $line[$c[2]],
                );
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function getByName($name) {
        $this->reset();
        $this->addColumns(Array("id", "name", "color",))
             ->addWhere("name", $name, "=");
        if($this->select()) {
            $c = $this->getColumns();
            $results = $this->getResult();
            if (empty($results)) {
                throw new Exception("Aucune guilde trouvée");
            }
            $line = $results[0];
            return new Guild(
                $line[$c[0]],
                $line[$c[1]],
                $line[$c[2]],
                );
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function add($name, $color): bool {
        $this->reset();
        if (!$this->insert(Array("name", "color"), Array($name, $color))) {
            throw new Exception($this->getError());
        }
        return true;
    }
}