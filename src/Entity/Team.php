<?php

namespace App\Entity;

class Team
{
    private int $_id;
    private int $_memberId;
    private string $_memberName;
    private int $_teamNb;
    private int $_hero1Id;
    private int $_hero2Id;
    private int $_hero3Id;
    private int $_hero4Id;

    public function __construct($id,
                                $memberId,
                                $teamNb,
                                $hero1Id,
                                $hero2Id,
                                $hero3Id,
                                $hero4Id,
                                $memberName= "") {
        $this->_id = (int) $id;
        $this->_memberId = (int) $memberId;
        $this->_memberName = $memberName;
        $this->_teamNb = $teamNb;
        $this->_hero1Id = (int) $hero1Id;
        $this->_hero2Id = (int) $hero2Id;
        $this->_hero3Id = (int) $hero3Id;
        $this->_hero4Id = (int) $hero4Id;
    }

    public function getId(): int { return $this->_id; }
    public function getMemberInfo(): array { return Array("id" => $this->_memberId, "name" => $this->_memberName); }
    public function getTeamNumber(): int { return $this->_teamNb; }
    public function getHero1Id(): int { return $this->_hero1Id; }
    public function getHero2Id(): int { return $this->_hero2Id; }
    public function getHero3Id(): int { return $this->_hero3Id; }
    public function getHero4Id(): int { return $this->_hero4Id; }
}