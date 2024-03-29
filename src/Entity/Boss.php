<?php

namespace App\Entity;

class Boss
{
    private $_id;
    private $_name;
    private $_nickname;

    public function __construct($id, $name, $nickname = "") {
        $this->_id = $id;
        $this->_name = $name;
        $this->_nickname = $nickname;
    }

    public function getId() { return $this->_id; }
    public function getName() { return $this->_name; }
    public function getNickname() { return $this->_nickname; }
}