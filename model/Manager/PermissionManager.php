<?php
include_once("AbstractManager.php");

class PermissionManager extends  AbstractManager
{
    const DB_NAME = 'permission';
    const DB_PREFIX = 'prm';

    public function __construct() {
        parent::__construct(PermissionManager::DB_NAME, PermissionManager::DB_PREFIX);
    }

    /**
     * @throws Exception
     */
    public function getAllInRawData() {
        $this->reset();
        $this->addColumns(Array("id", "name", "grade"));
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $entities[(int) $line[$c[0]]] = Array("name" => $line[$c[1]], "grade" => $line[$c[2]]);
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }

    public function getGradeByParam(string $param, $value): int {
        $this->reset();
        $this->addColumns(Array("grade"))
             ->addWhere($param, $value, "=");
        if($this->select()) {
            $c = $this->getColumns();
            $results = $this->getResult();
            if (sizeof($results) > 0) {
                $line = $this->getResult()[0];
                return (int) $line[$c[0]];
            }
            return 999;
        } else {
            throw new Exception($this->getError());
        }
    }

    public function getGradeByName($name): int {
        return $this->getGradeByParam("name", $name);
    }

    public function getGradeById($id): int {
        return $this->getGradeByParam("id", $id);
    }
}