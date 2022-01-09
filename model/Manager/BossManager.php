<?php
include_once("AbstractManager.php");
include_once("model/Entity/Boss.php");

class BossManager extends AbstractManager
{
    const DB_NAME = 'boss';
    const DB_PREFIX = 'bos';

    public function __construct() {
        parent::__construct(BossManager::DB_NAME, BossManager::DB_PREFIX);
    }

    public function getBossById($id) {
        $this->reset();
        $this->addColumns(Array("id", "name",))
             ->addWhere("id", strval($id), "=")
             ->addLimit(1);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = NULL;
            $results = $this->getResult();
            if (empty($results)) {
                throw new Exception("Aucun boss trouvé");
            }
            $line = $results[0];
            return new Boss($line[$c[0]], $line[$c[1]]);
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function getAll() {
        $this->reset();
        $this->addColumns(Array("id", "name"))
             ->addOrderBy("name", true);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $entities[(int) $line[$c[0]]] = new Boss(
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
