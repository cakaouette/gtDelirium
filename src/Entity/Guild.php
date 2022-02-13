<?php

namespace App\Entity;

class Guild
{
    private $_id;
    private $_name;
    private $_color;

    public function __construct($id, $name, $color) {
        $this->_id = $id;
        $this->_name = $name;
        $this->_color = $color;
    }

    public function getId() { return $this->_id; }
    public function getName() { return $this->_name; }
    public function getColor() { return $this->_color; }
}