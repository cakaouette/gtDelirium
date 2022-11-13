<?php

namespace App\Entity;

class BossStrategy
{
    private int $_id;
    private int $_bossId;
    private string $_strategy;

    public function __construct($id, $bossId, $strategy) {
        $this->_id = (int) $id;
        $this->_bossId = (int) $bossId;
        $this->_strategy = (string) $strategy;
    }

    public function getId() : int { return $this->_id; }
    public function getBossId() : int { return $this->_bossId; }
    public function getStrategy() : string { return $this->_strategy; }
}