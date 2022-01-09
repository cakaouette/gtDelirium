<?php
include_once("AbstractManager.php");
include_once("model/Entity/Ailment.php");

class AilmentManager extends AbstractManager
{
    const DB_NAME = 'ailment';
    const DB_PREFIX = 'ait';

    public function __construct() {
        parent::__construct(AilmentManager::DB_NAME, AilmentManager::DB_PREFIX);
    }
    
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