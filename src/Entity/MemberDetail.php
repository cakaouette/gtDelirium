<?php

namespace App\Entity;

class MemberDetail
{
    private $_id;
    private $_name;
    private $_tag;
    private $_discordId;
    private $_guildId;
    private $_guildName;
    private $_dateStart;
    private $_permId;
    private $_permName;
    private $_login;
    private $_passwd;
    private $_deleted;


    public function __construct($id, $name, $tag, $discordId,
                                $guildId, $guildName, $dateStart,
                                $permId, $permName, $login, $passwd,
                                $deleted) {
        $this->_id = $id;
        $this->_name = $name;
        $this->_tag = $tag;
        $this->_discordId = $discordId;
        $this->_guildId = $guildId;
        $this->_guildName = $guildName;
        $this->_dateStart = $dateStart;
        $this->_permId = $permId;
        $this->_permName = $permName;
        $this->_login = $login;
        $this->_passwd = $passwd;
        $this->_deleted = $deleted;
    }

    public function getId() { return $this->_id; }
    public function getName() { return $this->_name; }
    public function getTag() { return $this->_tag; }
    public function getDiscordId() { return $this->_discordId; }
    public function getGuildInfo() { return Array("id" => $this->_guildId, "name" => $this->_guildName); }
    public function getDateStart() { return $this->_dateStart; }
    public function getPermInfo() { return Array("id" => $this->_permId, "name" => $this->_permName); }
    public function getLogin() { return $this->_login; }
    public function getPasswd() { return $this->_passwd; }
    public function getDeleted() { return $this->_deleted; }
}