<?php
include_once("model/DatabaseClass.php");

class ElementManager
{
    private PDO $db;
    const DB_NAME = 'element';
    const DB_PREFIX = 'elt';

    public function __construct() {
//        parent::__construct(ElementManager::DB_NAME, ElementManager::DB_PREFIX);
        $this->db = \Gt\DatabaseClass::getInstance()->getDb();
    }

    public static function getAllInRawData() {
        $db = \Gt\DatabaseClass::getInstance()->getDb();
        $request = $db->query('SELECT * FROM element');
        $elements = array();
        while ($line = $request->fetch()) {
            $elements[$line["id"]] = $line["name"];
        }
        return $elements;
    }
}