<?php

namespace App\Manager;

use Exception;
use App\Entity\Element;

class ElementManager extends AbstractManager
{
    const DB_NAME = 'element';
    const DB_PREFIX = 'elt';

    protected function getTable() { return [ElementManager::DB_PREFIX, ElementManager::DB_NAME]; }

    public function getAllInRawData() {
        $db = $this->_db;
        $request = $db->query('SELECT * FROM element');
        $elements = array();
        while ($line = $request->fetch()) {
            $elements[$line["id"]] = $line["name"];
        }
        return $elements;
    }
    
    public function getAll() {
        $this->reset();
        $this->addColumns(Array("id", "name"))
             ->addOrderBy("id", false);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $entities[(int) $line[$c[0]]] = new Element(
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