<?php

namespace App\Entity;

class RaidDate
{
    private $_id;
    private $_date;

    public function __construct($id, $date){
        $this->_id = $id;
        $this->_date = $date;
    }

    public function getId() { return $this->_id; }
    public function getDate() { return $this->_date; }
}