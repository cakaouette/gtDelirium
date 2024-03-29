<?php

namespace App\Entity;

class Element
{
    private $_id;
    private $_name;

    public function __construct($id, $name) {
        $this->_id = $id;
        $this->_name = $name;
    }

    public function getId() { return $this->_id; }
    public function getName() { return $this->_name; }
}