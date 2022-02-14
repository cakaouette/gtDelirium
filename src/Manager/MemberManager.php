<?php

namespace App\Manager;

use PDO;
use Exception;
use Monolog\Logger;
use App\Entity\Team;
use App\Entity\Member;
use Laminas\Config\Config;
use App\Entity\MemberDetail;

class MemberManager extends AbstractManager
{
    public const DB_NAME = 'member';
    public const DB_PREFIX = 'mbr';

    private string $salt;

    public function __construct(Config $config, DatabaseClass $db, Logger $logger) {
        parent::__construct($db, $logger);
        $this->salt = $config->get('salt');
    }

    protected function getTable() { return [MemberManager::DB_PREFIX, MemberManager::DB_NAME]; }

    /**
     * @throws Exception
     */
    public function getAll() {
        $this->reset();
        $this->addColumns(Array("id", "name", "guildId", "dateStart", "deleted"))
             ->addColumns(Array("name"), false, GuildManager::DB_NAME, GuildManager::DB_PREFIX)
             ->addJoin("LEFT", "guildId", "id", GuildManager::DB_NAME)
             ->addWhere("deleted", "1", "!=")
             ->addOrderBy("name", true);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $entities[(int) $line[$c[0]]] = new Member(
                        $line[$c[0]],
                        $line[$c[1]],
                        $line[$c[2]],
                        $line[$c[3]],
                        $line[$c[4]],
                        $line[$c[5]],
                        );
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }

    /**
     * @throws Exception
     */
    public function getAllByGuildId($guildId, $memberFilter = NULL) {
        $this->reset();
        $this->addColumns(Array("id", "name", "guildId", "dateStart", "deleted"))
             ->addColumns(Array("name"), false, GuildManager::DB_NAME, GuildManager::DB_PREFIX)
             ->addJoin("LEFT", "guildId", "id", GuildManager::DB_NAME)
             ->addWhere("deleted", "1", "!=")
             ->addWhere("guildId", $guildId, "=")
             ->addOrderBy("name", true);
        if (!is_null($memberFilter)) {
          $this->addWhere("id", $memberFilter, "=");
        }
        if($this->select()) {
            $c = $this->getColumns();
            $entities = array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $entities[(int) $line[$c[0]]] = new Member(
                        $line[$c[0]],
                        $line[$c[1]],
                        $line[$c[2]],
                        $line[$c[3]],
                        $line[$c[4]],
                        $line[$c[5]],
                        );
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function countMemberByGuildId() {
        $this->reset();
        $this->addColumns(Array("guildId"))
            ->addWhere("guildId", "0", "!=")
            ->addfunction("count", "id")
            ->addGroupBy("guildId");
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $entities[(int) $line[$c[0]]] = $line[$c[1]];
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }

    public function getAllByGuildIdInRawData($guildId, $date = NULL, bool $withNullLine = true) {
        $db = $this->_db;
        $dateQueryStr = !is_null($date) ? "AND dateStart <= :dateStartQuery " : "";
        $query = 'SELECT * FROM member WHERE guildId LIKE :guildIdQuery '. $dateQueryStr .'ORDER BY name ASC';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':guildIdQuery', $guildId, PDO::PARAM_INT);
        if (!is_null($date)) {
            $stmt->bindParam(':dateStartQuery', $date, PDO::PARAM_STR);
        }

        if (!$stmt->execute()) {
            return Array();
        }
        $entities = $withNullLine ? array(0 => "") : array();
        while ($entity = $stmt->fetch()) {
            $entities[(int) $entity["id"]] = $entity["name"];
        }
        return $entities;
    }

    public function getAllWithDateStartByGuildIdInRawData($guildId, $date = NULL, bool $withNullLine = true) {
        $db = $this->_db;
        $dateQueryStr = !is_null($date) ? "AND dateStart <= :dateStartQuery " : "";
        $query = 'SELECT * FROM member WHERE guildId LIKE :guildIdQuery '. $dateQueryStr .'ORDER BY name ASC';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':guildIdQuery', $guildId, PDO::PARAM_INT);
        if (!is_null($date)) {
            $stmt->bindParam(':dateStartQuery', $date, PDO::PARAM_STR);
        }

        if (!$stmt->execute()) {
            return Array();
        }
        $entities = $withNullLine ? array(0 => "") : array();
        while ($entity = $stmt->fetch()) {
            $entities[(int) $entity["id"]] = Array("name" => $entity["name"], "dateStart" => $entity["dateStart"]);
        }
        return $entities;
    }

    public function addMember($name, $guildId, $dateStart) {
        $this->reset();
        if (!$this->insert(Array("name", "guildId", "dateStart", "permId", "login", "passwd", "deleted"),
                           Array($name, $guildId, $dateStart, "5", "", "", 0))) {
            throw new Exception($this->getError());
        }
        return true;
    }

    public function getById($id) {
        $this->reset();
        $this->addColumns(Array("id", "name", "guildId", "dateStart", "deleted",))
             ->addColumns(Array("name"), false, GuildManager::DB_NAME, GuildManager::DB_PREFIX)
             ->addWhere("id", strval($id), "=")
             ->addWhere("deleted", strval(1), "!=")
             ->addJoin("LEFT", "guildId", "id", GuildManager::DB_NAME);
        if($this->select()) {
            $c = $this->getColumns();
            $results = $this->getResult();
            if (empty($results)) {
                throw new Exception("Aucun membre trouvé");
            }
            $line = $results[0];
            return new Member(
                    $line[$c[0]],
                    $line[$c[1]],
                    $line[$c[2]],
                    $line[$c[3]],
                    $line[$c[4]],
                    $line[$c[5]],
            );
        } else {
            throw new Exception($this->getError());
        }
    }

    public function getByLogin($login) {
        $this->reset();
        $this->addColumns(Array("id", "name", "tag", "discordId",
                                "guildId", "dateStart",
                                "permId", "login", "passwd", "deleted"))
             ->addColumns(Array("name"), false, GuildManager::DB_NAME, GuildManager::DB_PREFIX)
             ->addColumns(Array("name"), false, PermissionManager::DB_NAME, PermissionManager::DB_PREFIX)
             ->addWhere("login", strval($login), "=")
             ->addJoin("LEFT", "guildId", "id", GuildManager::DB_NAME)
             ->addJoin("LEFT", "permId", "id", PermissionManager::DB_NAME);
        if($this->select()) {
            $c = $this->getColumns();
            $results = $this->getResult();
            if (empty($results)) {
                throw new Exception("Aucun membre trouvé");
            }
            $line = $results[0];
            return new MemberDetail(
                    $line[$c[0]], //id
                    $line[$c[1]], //name
                    $line[$c[2]], //tag
                    $line[$c[3]], //discordId
                    $line[$c[4]], //guildId
                    $line[$c[10]], //guildName
                    $line[$c[5]], //dateStart
                    $line[$c[6]], //permId
                    $line[$c[11]], //permName
                    $line[$c[7]], //login
                    $line[$c[8]], //passwd
                    $line[$c[9]], //deleted
            );
        } else {
            throw new Exception($this->getError());
        }
    }

    public function getDetailById($id) {
        $this->reset();
        $this->addColumns(Array("id", "name", "tag", "discordId",
                                "guildId", "dateStart",
                                "permId", "login", "passwd", "deleted"))
             ->addColumns(Array("name"), false, GuildManager::DB_NAME, GuildManager::DB_PREFIX)
             ->addColumns(Array("name"), false, PermissionManager::DB_NAME, PermissionManager::DB_PREFIX)
             ->addWhere("id", strval($id), "=")
             ->addJoin("LEFT", "guildId", "id", GuildManager::DB_NAME)
             ->addJoin("LEFT", "permId", "id", PermissionManager::DB_NAME);
        if($this->select()) {
            $c = $this->getColumns();
            $results = $this->getResult();
            if (empty($results)) {
                throw new Exception("Aucun membre trouvé");
            }
            $line = $results[0];
            return new MemberDetail(
                    $line[$c[0]], //id
                    $line[$c[1]], //name
                    $line[$c[2]], //tag
                    $line[$c[3]], //discordId
                    $line[$c[4]], //guildId
                    $line[$c[10]], //guildName
                    $line[$c[5]], //dateStart
                    $line[$c[6]], //permId
                    $line[$c[11]], //permName
                    $line[$c[7]], //login
                    $line[$c[8]], //passwd
                    $line[$c[9]], //deleted
            );
        } else {
            throw new Exception($this->getError());
        }
    }

    public function updateMember($id, array $params) /*$name, $guildId, $dateStart)*/ {
        $this->reset();
        if(!$this->update($id, array_keys($params), array_values($params))) {
            throw new Exception($this->getError());
        }
        return true;
    }

    public function getRawGuild() {
        return $this->_guilds;
    }

    public function getTeamsByGuild($guildId, $memberFilter = NULL) {
        $this->reset();
        $this->addColumns(Array("id", "name"))
            ->addColumns(Array("id", "teamNumber", "hero1Id", "hero2Id", "hero3Id", "hero4Id"),
                    false, TeamManager::DB_NAME, TeamManager::DB_PREFIX)
            ->addJoin("LEFT", "id", "memberId", TeamManager::DB_NAME)
            ->addWhere("guildId", strval($guildId), "=")
            ->addOrderBy("name", true)
            ->addOrderBy("teamNumber", true, TeamManager::DB_NAME);
        if (!is_null($memberFilter)) {
          $this->addWhere("memberId", $memberFilter, "=", TeamManager::DB_NAME);
        }
        if($this->select()) {
            $c = $this->getColumns();
            $ret = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
              if (is_null($line[$c[3]])) { continue;}
              $entity = new Team($line[$c[2]],
                              $line[$c[0]],
                              $line[$c[3]],
                              $line[$c[4]],
                              $line[$c[5]],
                              $line[$c[6]],
                              $line[$c[7]],
                              $line[$c[1]]);
              if (!array_key_exists($line[$c[0]], $ret)) {
                $ret[$line[$c[0]]] = ["teams" => [$line[$c[3]] => $entity]];
              } else {
                $ret[$line[$c[0]]]["teams"][$line[$c[3]]] = $entity;
              }
            }
            return $ret;
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function getDiscordId($memberId) {
        $this->reset();
        $this->addColumns(Array("id", "discordId"))
             ->addWhere("id", strval($memberId), "=");
        if($this->select()) {
            $c = $this->getColumns();
            $results = $this->getResult();
            if (empty($results)) {
                throw new Exception("Aucun discord id trouvé");
            }
            $line = $results[0];
            return Array("memberId" => $line[$c[0]], "discordId" => $line[$c[1]]);
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function updateLoginPasswd($id, $login, $passwd) {
        $this->reset();
        if(!$this->update($id, Array("login", "passwd"),
                               Array($login, $this->cryptPassword($passwd, $login)))) {
            throw new Exception($this->getError());
        }
        return true;
    }

    //not really the place to do it, but better than in the controller
    //TODO move somewhere else when possible
    public function isPasswdCorrect(MemberDetail $member, string $passwd)
    {
        return $member->getPasswd() == $this->cryptPassword($passwd, $member->getLogin());
    }

    public function cryptPassword($passwd, $login) {
        return md5($passwd.$this->salt.$login);

    }
}