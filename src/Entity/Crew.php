<?php

namespace App\Entity;

class Crew
{
    private $_id;
    private $_name;
    private $_elementId;
    private $_grade;
    private $_level;
    private $_evolvedGrade;
    private $_nbBreak;
    private bool $_hasExclusiveWeapon;
    private $_nbWeaponBreak;
    private $_crewId;


    public function __construct($id, $name, $elementId, $grade,
            $level, $evolvedGrade, $nbBreak, $hasExclusiveWeapon, $nbWeaponBreak, $crewId) {
        $this->_id = $id;
        $this->_name = $name;
        $this->_elementId = $elementId;
        $this->_grade = $grade;
        $this->_level = $level;
        $this->_evolvedGrade = $evolvedGrade;
        $this->_nbBreak = $nbBreak;
        $this->_hasExclusiveWeapon = (!is_null($hasExclusiveWeapon) and $hasExclusiveWeapon > 0) ? true : false;
        $this->_nbWeaponBreak = $nbWeaponBreak;
        $this->_crewId = $crewId;
    }

    public function getId() { return $this->_id; }
    public function getCrewId() { return $this->_crewId; }
    public function getName() { return $this->_name; }
    public function getElementId() { return $this->_elementId; }
    public function getGrade() { return $this->_grade; }
    public function getLevel() { return $this->_level; }
    public function getEvolvedGrade() { return $this->_evolvedGrade; }
    public function getNumberBreak() { return $this->_nbBreak; }
    public function hasExclusiveWeapon(): bool { return $this->_hasExclusiveWeapon; }
    public function getNumberWeaponBreak() { return $this->_nbWeaponBreak; }
}