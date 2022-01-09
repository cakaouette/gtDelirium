<?php


class Weapon
{
    private $_id;
    private $_name;
    private $_ailmentId;
    private $_characId;
    private $_aeId;
    private $_bossId;
    private $_rate;
    private $_characName;

    public function __construct($id, $name, $ailmentId, $characId,
            $aeId = "", $bossId = "", $rate = "", $characName = "") {
        $this->_id = $id;
        $this->_name = $name;
        $this->_ailmentId = $ailmentId;
        $this->_characId = $characId;
        $this->_aeId = $aeId ?? NULL;
        $this->_bossId = $bossId;
        $this->_rate = $rate ?? NULL;
        $this->_characName = $characName;
    }

    public function getId(): int { return $this->_id; }
    public function getName(): string { return $this->_name; }
    public function getAeId() { return $this->_aeId; }
    public function getAilmentInfo(): Array { return Array("id" => $this->_ailmentId, "name" => ""); }
    public function getBossInfo(): Array { return Array("id" => $this->_bossId, "name" => ""); }
    public function getCharacInfo(): Array { return Array("id" => $this->_characId, "name" => $this->_characName); }
    public function getRate() { return $this->_rate; }
}