<?php

namespace App\Entity;

class Raid
{
    private $_id;
    private $_date;
    private $_boss1 = Array("id" => 0, "name" => "", "shortName" => "", "element" => 0, "e_name" => "");
    private $_boss2 = Array("id" => 0, "name" => "", "shortName" => "", "element" => 0, "e_name" => "");
    private $_boss3 = Array("id" => 0, "name" => "", "shortName" => "", "element" => 0, "e_name" => "");
    private $_boss4 = Array("id" => 0, "name" => "", "shortName" => "", "element" => 0, "e_name" => "");

    public function __construct($id, $date,
                                $boss1Id, $boss1Name, $boss1ShortName, $element1Id, $element1Name,
                                $boss2Id, $boss2Name, $boss2ShortName, $element2Id, $element2Name,
                                $boss3Id, $boss3Name, $boss3ShortName, $element3Id, $element3Name,
                                $boss4Id, $boss4Name, $boss4ShortName, $element4Id, $element4Name){
        $this->_id = $id;
        $this->_date = $date;
        $this->_boss1 = Array("id" => $boss1Id,
                              "name" => $boss1Name,
                              "shortName" => $boss1ShortName,
                              "element" => $element1Id,
                              "e_name" => $element1Name);
        $this->_boss2 = Array("id" => $boss2Id,
                              "name" => $boss2Name,
                              "shortName" => $boss2ShortName,
                              "element" => $element2Id,
                              "e_name" => $element2Name);
        $this->_boss3 = Array("id" => $boss3Id,
                              "name" => $boss3Name,
                              "shortName" => $boss3ShortName,
                              "element" => $element3Id,
                              "e_name" => $element3Name);
        $this->_boss4 = Array("id" => $boss4Id,
                              "name" => $boss4Name,
                              "shortName" => $boss4ShortName,
                              "element" => $element4Id,
                              "e_name" => $element4Name);
    }

    public function getId() { return $this->_id; }
    public function getDate() { return $this->_date; }
    public function getBoss1Info() { return $this->_boss1; }
    public function getBoss2Info() { return $this->_boss2; }
    public function getBoss3Info() { return $this->_boss3; }
    public function getBoss4Info() { return $this->_boss4; }
}
