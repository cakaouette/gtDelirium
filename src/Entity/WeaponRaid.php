<?php

namespace App\Entity;

class WeaponRaid
{
    private $_id;
    private $_name;
    private $_ailmentId;
    private $_characId;
    private $_bossId1;
    private $_rate1;
    private $_bossId2;
    private $_rate2;
    private $_bossId3;
    private $_rate3;
    private $_bossId4;
    private $_rate4;
    private $_characName;

    public function __construct($id, $name, $ailmentId, $characId,
            $bossId1 = "", $rate1 = "",
            $bossId2 = "", $rate2 = "",
            $bossId3 = "", $rate3 = "",
            $bossId4 = "", $rate4 = "",
            $characName = "") {
        $this->_id = $id;
        $this->_name = $name;
        $this->_ailmentId = $ailmentId;
        $this->_characId = $characId;
        $this->_bossId1 = $bossId1 ?? NULL;
        $this->_rate1 = $rate1 ?? NULL;
        $this->_bossId2 = $bossId2 ?? NULL;
        $this->_rate2 = $rate2 ?? NULL;
        $this->_bossId3 = $bossId3 ?? NULL;
        $this->_rate3 = $rate3 ?? NULL;
        $this->_bossId4 = $bossId4 ?? NULL;
        $this->_rate4 = $rate4 ?? NULL;
        $this->_characName = $characName;
    }

    public function getId(): int { return $this->_id; }
    public function getName(): string { return $this->_name; }
    public function getAilmentInfo(): Array { return Array("id" => $this->_ailmentId, "name" => ""); }
    public function getBossInfo(): Array { return Array("id" => $this->_bossId, "name" => ""); }
    public function getCharacInfo(): Array { return Array("id" => $this->_characId, "name" => $this->_characName); }
    public function getRates(): Array { return Array("rate1" => $this->_rate1,
        "rate2" => $this->_rate2,
        "rate3" => $this->_rate3,
        "rate4" => $this->_rate4); }
}