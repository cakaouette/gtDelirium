<?php

namespace App\Entity;

class Pending
{
    private int $_id;
    private string $_pseudo;
    private string $_login;
    private string $_passwd;

    public function __construct(int $id, string $pseudo, string $login, string $passwd) {
        $this->_id = $id;
        $this->_pseudo = $pseudo;
        $this->_login = $login;
        $this->_passwd = $passwd;
    }

    public function getId(): int { return $this->_id; }
    public function getPseudo(): string { return $this->_pseudo; }
    public function getLogin(): string { return $this->_login; }
    public function getPasswd(): string { return $this->_passwd; }
}