<?php

namespace App\Manager;

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
}