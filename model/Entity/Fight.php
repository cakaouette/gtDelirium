<?php


class Fight
{
    private int $_id;
    private int $_pseudoId;
    private string $_pseudoName;
    private int $_guildId;
    private string $_guildName;
    private int $_raidId;
    private string $_date;
    private int $_teamNb;
    private $_bossId;
    private $_bossName;
    private $_damage;
    private $_hero1Id;
    private $_hero2Id;
    private $_hero3Id;
    private $_hero4Id;
    private string $_hero1Name;
    private string $_hero2Name;
    private string $_hero3Name;
    private string $_hero4Name;

    public function __construct($id,
                                $pseudoId,
                                $guildId,
                                $raidId,
                                $date,
                                $teamNb,
                                $bossId,
                                $damage,
                                $hero1Id,
                                $hero2Id,
                                $hero3Id,
                                $hero4Id,
                                $bossName = "",
                                $hero1Name = "",
                                $hero2Name = "",
                                $hero3Name = "",
                                $hero4Name = "",
                                $pseudoName= "",
                                $guildName= "") {
        $this->_id = (int) $id;
        $this->_pseudoId = (int) $pseudoId;
        $this->_pseudoName = $pseudoName;
        $this->_guildId = (int) $guildId;
        $this->_guildName = $guildName;
        $this->_raidId = (int) $raidId;
        $this->_date = $date;
        $this->_teamNb = $teamNb;
        $this->_bossId = $bossId;
        $this->_bossName = $bossName;
        $this->_damage = $damage;
        $this->_hero1Id = $hero1Id;
        $this->_hero2Id = $hero2Id;
        $this->_hero3Id = $hero3Id;
        $this->_hero4Id = $hero4Id;
        $this->_hero1Name = $hero1Name;
        $this->_hero2Name = $hero2Name;
        $this->_hero3Name = $hero3Name;
        $this->_hero4Name = $hero4Name;
    }

    public function getId(): int { return $this->_id; }
    public function getPseudoInfo(): array { return Array("id" => $this->_pseudoId, "name" => $this->_pseudoName); }
    public function getGuildInfo(): array { return Array("id" => $this->_guildId, "name" => $this->_guildName); }
    public function getRaidId(): int { return $this->_raidId; }
    public function getDate(): string { return $this->_date; }
    public function getTeamNumber(): int { return $this->_teamNb; }
    public function getBossInfo(): array { return Array("id" => $this->_bossId, "name" => $this->_bossName); }
    public function getDamage() { return $this->_damage; }
    public function getHero1Info(): array { return Array("id" => $this->_hero1Id, "name" => $this->_hero1Name); }
    public function getHero2Info(): array { return Array("id" => $this->_hero2Id, "name" => $this->_hero2Name); }
    public function getHero3Info(): array { return Array("id" => $this->_hero3Id, "name" => $this->_hero3Name); }
    public function getHero4Info(): array { return Array("id" => $this->_hero4Id, "name" => $this->_hero4Name); }
}