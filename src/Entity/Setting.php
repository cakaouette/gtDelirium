<?php

namespace App\Entity;

class Setting
{
    private $_id;
    private $_memberId;
    private $_maxLevel;

    public function __construct($id, $memberId, $maxLevel) {
        $this->_id = $id;
        $this->_memberId = $memberId;
        $this->_maxLevel = $maxLevel;
    }

    public function getId() { return $this->_id; }
    public function getMemberId() { return $this->_memberId; }
    public function getMaxHeroLevel() { return $this->_maxLevel; }
}