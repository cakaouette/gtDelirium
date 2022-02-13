<?php

namespace App\Entity;

class Member
{
    private $_id;
    private $_name;
    private $_guildId;
    private $_guildName;
    private $_dateStart;
    private $_deleted;
    private $_permId;
    private $_login;
    private $_passwd;


    public function __construct($id, $name, $guildId, $dateStart, $deleted,
                                $guildName = "", $permId = 0, $login = "", $passwd = "") {
        $this->_id = $id;
        $this->_name = $name;
        $this->_guildId = $guildId;
        $this->_guildName = $guildName;
        $this->_dateStart = $dateStart;
        $this->_deleted = $deleted;
        $this->_permId = $permId;
        $this->_login = $login;
        $this->_passwd = $passwd;
    }

    public function getId() { return $this->_id; }
    public function getName() { return $this->_name; }
    public function getGuildInfo() { return Array("id" => $this->_guildId, "name" => $this->_guildName); }
    public function getDateStart() { return $this->_dateStart; }
    public function getDeleted() { return $this->_deleted; }
    public function getPermId() { return $this->_permId; }
    public function getLogin() { return $this->_login; }
    public function getPasswd() { return $this->_passwd; }
}