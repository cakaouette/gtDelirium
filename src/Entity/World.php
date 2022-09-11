<?php

namespace App\Entity;

class World
{
    private $_id;
    private $_number;
    private $_maxLevel;
    private $_disable;

    public function __construct($id, $number, $maxLevel, $disable) {
        $this->_id = $id;
        $this->_number = $number;
        $this->_maxLevel = $maxLevel;
        $this->_disable = $disable;
    }

    public function getId() { return $this->_id; }
    public function getNumber() { return $this->_number; }
    public function getMaxLevel() { return $this->_maxLevel; }
    public function isDisabled() { return $this->_disable; }
}