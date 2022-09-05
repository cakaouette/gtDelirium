<?php

namespace App\Entity;

class RaidDate
{
    private $_id;
    private $_date;
    private $_duration;

    public function __construct($id, $date, $duration){
        $this->_id = $id;
        $this->_date = $date;
        $this->_duration = $duration;
    }

    public function getId() { return $this->_id; }
    public function getDate() { return $this->_date; }
    public function getDuration() { return $this->_duration; }
}