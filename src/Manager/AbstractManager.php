<?php

namespace App\Manager;

use PDO;
use PDOException;
use Monolog\Logger;

abstract class AbstractManager
{
    protected PDO $_db;
    private string $_table;
    private string $_prefix;

    private Logger $_logger;
    private string $_error = "";

    private array $_columnsReturned = array();
    private int   $_count = 0;
    private array $_params = array();
    private array $_values = array();
    private array $_columns = array();
    private array $_joins = array();
    private array $_wheres = array();
    private array $_orders = array();
    private array $_groups = array();
    private int   $_limit = 0;
    private array $_result = array();

    public function __construct(DatabaseClass $db, Logger $logger) {
        $this->_db = $db->getDb();
        $this->_logger = $logger;
        [$prefix, $table] = $this->getTable();
        $this->_table = $table;
        $this->_prefix = $prefix;
    }

    abstract protected function getTable();

    public function reset() {
        $this->_columnsReturned = array();
        $this->_count = 0;
        $this->_params = array();
        $this->_values = array();
        $this->_columns = array();
        $this->_joins = array();
        $this->_wheres = array();
        $this->_orders = array();
        $this->_groups = array();
        $this->_limit = 0;
        $this->_result = array();
    }

    public function excuseCustomQuery($queryString) {
        return $this->execute($queryString);
    }
    
    private function execute($queryString): bool {
        $this->_result = array();
//        print("queryString= $queryString<br>");
        $q = $this->_db->prepare($queryString);
        if (sizeof($this->_params) != sizeof($this->_values)) {
            $this->_error = "bind ERROR '$this->_table': nb params= ".sizeof($this->_params)." different de nb values= ".$this->_values;
            return false;
        }
        for ($i = 0; $i < sizeof($this->_params); $i++) {
            $q->bindParam($this->_params[$i], $this->_values[$i]);
        }
        try {
            $isExecOk = $q->execute();
        } catch (PDOException $e) {
            $this->_error = ""; //"PDO EXCEPTION '$this->_table': ".$e->getMessage();
            return false;
        }
        ob_start();
        $q->debugDumpParams();
        $logs = ob_get_contents();
        ob_end_clean();
        $this->_logger->debug("DEBUG\ " . $logs);
        if ($isExecOk) {
            while ($line = $q->fetch()) { array_push($this->_result, $line); }
        } else {
            $queryString = "INSERT INTO error (type, code, msg, comment) 
                      VALUES ('SQL', '5001-".$q->errorCode()."', \"$q->queryString\", '".implode(", ", $this->_params).", ".implode(", ", $this->_values)."')";
            $qe = $this->_db->prepare($queryString);
            $qe->execute();
            ob_start();
            $qe->debugDumpParams();
            $logs = ob_get_contents();
            ob_end_clean();
            $this->_logger->debug("SQL ERROR\ " . $logs);
            $qe = $this->_db->query("SELECT code, id FROM error ORDER BY id DESC LIMIT 1");
            $err = $qe->fetch();
            $code = $err["code"];
            $id = $err["id"];
            $this->_error = /*"SQL EXECUTE '$this->_table': ".*/"donner le code $id au webmaster<br>";
        }
        return $isExecOk;
    }

    private function addParams(array $params, string $table = NULL): array {
        $ret = array();
        foreach ($params as $name => $value) {
            $t = is_null($table) ? $this->_table : $table;
            $str = ":$t"."_$name"."Query".$this->_count++;
            $this->_params[] = $str;
            $this->_values[] = $value;
            $ret[] = $str;
        }
        return $ret;
    }

    public function getResult() {
        return $this->_result;
    }

    public function getColumns() {
        return $this->_columnsReturned;
    }

    public function getError(): string {
        return $this->_error;
    }

    public function addColumns(array $columns, $withRename = false, $table = NULL, $prefix = NULL): AbstractManager {
        $t = is_null($table) ? $this->_table : $table;
        $p = (is_null($prefix) ? $this->_prefix : $prefix)."_";
        if ($withRename) {
            foreach ($columns as $name => $rename) {
                $this->_columns[] = "$t.$name as $rename";
                $this->_columnsReturned[] = $rename;
            }
        } else {
            foreach ($columns as $name) {
                $this->_columns[] = "$t.$name as ".$p.$name;
                $this->_columnsReturned[] = $p.$name;
           }
        }
        return $this;
    }

    public function addfunction(string $func, string $name, string $rename = NULL, $table = NULL): AbstractManager {
        $t = is_null($table) ? $this->_table : $table;
        $r = is_null($rename) ? $func."_".$t."_".$name : $rename;
        $this->_columns[] = "$func($t.$name) as $r";
        $this->_columnsReturned[] = $r;
        return $this;
    }

    public function addJoin(string $type, string $firstCol, string $secondCol, string $secondTable, $firstTable = NULL): AbstractManager {
        $ft = is_null($firstTable) ? $this->_table : $firstTable;
        $st = $secondTable;
        $this->_joins[] = "$type JOIN $st ON $ft.$firstCol = $st.$secondCol";
        return $this;
    }

    private function where(string $name, string $value, string $comp, $table): string {
        $t = is_null($table) ? $this->_table : $table;
        $str = "";
        if ($comp != "is NULL") {
            $str = $this->addParams(Array($name => $value), $table)[0];
        }
        return "$t".".$name"." $comp $str";
    }

    private function inWhere(string $name, string $value1, string $value2, $table): string {
        $t = is_null($table) ? $this->_table : $table;
        $str1 = $this->addParams(Array($name => $value1), $table)[0];
        $str2 = $this->addParams(Array($name => $value2), $table)[0];
        return "$t".".$name"." BETWEEN $str1 AND $str2";
    }

    public function addWhere(string $name, string $value, string $comp, string $table = NULL): AbstractManager {
        $this->_wheres[] = Array("type" => "AND", "str" => $this->where($name, $value, $comp, $table));
        return $this;
    }

    public function orWhere(string $name, string $value, string $comp, string $table = NULL): AbstractManager {
        $this->_wheres[] = Array("type" => "OR", "str" => $this->where($name, $value, $comp, $table));
        return $this;
    }

    public function betweenWhere(string $name, string $value1, string $value2, string $table = NULL): AbstractManager {
        $this->_wheres[] = Array("type" => "AND", "str" => $this->inWhere($name, $value1, $value2, $table));
        return $this;
    }

    public function addOrderBy(string $name, bool $isAsc, string $table = NULL): AbstractManager {
        $t = is_null($table) ? $this->_table : $table;
        $this->_orders[] = "$t".".$name"."  ".($isAsc ? "ASC" : "DESC");
        return $this;
    }

    public function addGroupBy(string $name, string $table = NULL): AbstractManager {
        $t = is_null($table) ? $this->_table : $table;
        $this->_groups[] = "$t".".$name";
        return $this;
    }

    public function addLimit(int $limit): AbstractManager {
        $this->_limit = strval($limit);
        return $this;
    }

    public function insert(array $columns, array $values): bool { // TODO passer par addParam
        // Check no null params
        if (empty($columns)) {
            $this->_error = "INSERT ERROR '$this->_table': Pas de colonne definie";
            return false;
        }
        if (empty($values)) {
            $this->_error = "INSERT ERROR '$this->_table': Pas de valeur definie";
            return false;
        }
        if (sizeof($columns) != sizeof($values)) {
            $this->_error = "INSERT ERROR '$this->_table': nb params= ".sizeof($this->_columns)." different de nb values= ".$this->_table;
            return false;
        }

        // prepare queryString
        $columnNamesQueryString = implode(", ", $columns);
        foreach ($values as  &$value) {
            $value= is_null($value) ? "NULL" : "'".$value."'";
        }
        $valuesQueryString = implode(", ", $values);
        $queryString = "INSERT INTO $this->_table ($columnNamesQueryString) VALUES ($valuesQueryString)";

        // execute
        return $this->execute($queryString);
    }
    
    private function implodeWhere(): string {
        if (empty($this->_wheres)) {
            return "";
        }
        $str = " WHERE ".$this->_wheres[0]["str"];
        for ($i = 1; $i < sizeof($this->_wheres); $i++) {
           $str =  $str." ".$this->_wheres[$i]["type"]." ".$this->_wheres[$i]["str"];
        }
        return $str;
    }

    public function select(): bool {
        $columnNamesQueryString = implode(", ", $this->_columns);
        $joinQueryString = !empty($this->_joins) ? " ".implode(" ", $this->_joins) : "";
        $whereQueryString = $this->implodeWhere();
        $orderByQueryString = !empty($this->_orders) ? " ORDER BY ".implode(", ", $this->_orders) : "";
        $groupByQueryString = !empty($this->_groups) ? " GROUP BY ".implode(", ", $this->_groups) : "";
        $limitQueryString = ($this->_limit > 0) ? " LIMIT ".$this->_limit : "";
        $queryString = "SELECT $columnNamesQueryString FROM $this->_table"
                        ."$joinQueryString"
                        ."$whereQueryString"
                        ."$groupByQueryString"
                        ."$orderByQueryString"
                        ."$limitQueryString";
        return $this->execute($queryString);
    }

    public function delete(): bool {
        $whereQueryString = $this->implodeWhere();
        $queryString = "DELETE FROM $this->_table"
                        ."$whereQueryString";
        return $this->execute($queryString);
    }
    
    public function update($id, array $columns, array $values): bool {
        //        $setString = implode(", ", $setQuery);
        // $stmt = $this->_db->prepare("UPDATE member SET $setString WHERE id LIKE :idQuery");
        // Check no null params
        if (empty($columns)) {
            $this->_error = "INSERT ERROR '$this->_table': Pas de colonne definie";
            return false;
        }
        if (empty($values)) {
            $this->_error = "INSERT ERROR '$this->_table': Pas de valeur definie";
            return false;
        }
        if (sizeof($columns) != sizeof($values)) {
            $this->_error = "INSERT ERROR '$this->_table': nb params= ".sizeof($this->_columns)." different de nb values= ".$this->_table;
            return false;
        }
        $setQuery = Array();
        foreach ($values as  &$value) {
            $value= is_null($value) ? "NULL" : "'".$value."'";
        }
        for ($i = 0; $i < sizeof($columns); $i++) {
            $setQuery[] = "$columns[$i]=$values[$i]";
        }
        $setString = implode(", ", $setQuery);
        
        $this->addWhere("id", $id, "=");
        $whereQueryString = $this->implodeWhere();
        $queryString = "UPDATE $this->_table SET $setString"
                        ."$whereQueryString";
        return $this->execute($queryString);
    }
}