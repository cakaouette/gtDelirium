<?php

namespace App\Manager;

use Exception;
use App\Entity\Pending;

class PendingManager extends AbstractManager
{
    const DB_NAME = 'pending';
    const DB_PREFIX = 'pdg';

    protected function getTable() { return [PendingManager::DB_PREFIX, PendingManager::DB_NAME]; }

    public function addPending($pseudo, $login, $passwd): bool {
        $this->reset();
        if (!$this->insert(Array("pseudo", "login", "passwd"), Array($pseudo, $login, $passwd))) {
            throw new Exception($this->getError());
        }
        return true;
    }

    public function deletePending($id): bool {
        $this->reset();
        $this->addWhere("id", strval($id), "=");
        if (!$this->delete()) {
            throw new Exception($this->getError());
        }
        return true;
    }

    public function getAll() {
        $this->reset();
        $this->addColumns(Array("id", "pseudo", "login", "passwd"))
             ->addOrderBy("pseudo", true);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = array();
            $results = $this->getResult();
            if (empty($results)) {
                throw new Exception("Aucun utilisateur en attente");
            }
            foreach ($results as $line) {
                $entities[(int) $line[$c[0]]] = new Pending(
                    $line[$c[0]],
                    $line[$c[1]],
                    $line[$c[2]],
                    $line[$c[3]]
            );
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }

    /**
     * @throws Exception
     */
    public function getPendingById($id) {
        $this->reset();
        $this->addColumns(Array("id", "pseudo", "login", "passwd"));
        $this->addWhere("id", "$id", "=");
        if($this->select()) {
            $c = $this->getColumns();
            $results = $this->getResult();
            if (empty($results)) {
                throw new Exception("Utilisateur non trouvé");
            }
            $result = $results[0];
            return new Pending(
                $result[$c[0]],
                $result[$c[1]],
                $result[$c[2]],
                $result[$c[3]]
            );
        } else {
            throw new Exception($this->getError());
        }
    }

    
    public function getPendingByPseudo($pseudo) {
        $this->reset();
        $this->addColumns(Array("id", "pseudo", "login", "passwd"));
        $this->addWhere("pseudo", "$pseudo", "=");
        if($this->select()) {
            $c = $this->getColumns();
            $results = $this->getResult();
            if (empty($results)) {
                return NULL;
            }
            $result = $results[0];
            return new Pending(
                $result[$c[0]],
                $result[$c[1]],
                $result[$c[2]],
                $result[$c[3]]
            );
        } else {
            throw new Exception($this->getError());
        }
    }
    
}