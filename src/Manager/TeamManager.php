<?php

namespace App\Manager;

use PDO;
use Exception;
use PDOException;
use App\Entity\Team;

class TeamManager extends AbstractManager
{
    const DB_NAME = 'team';
    const DB_PREFIX = 'tem';
    
    protected function getTable() { return [TeamManager::DB_PREFIX, TeamManager::DB_NAME]; }

    public function getAllByMember($memberId) {
        $this->reset();
        $this->addColumns(Array("id", "memberId", "teamNumber",
            "hero1Id", "hero2Id", "hero3Id", "hero4Id"))
            ->addColumns(Array("name"), false, "member", "mbr")
            ->addJoin("LEFT", "memberId", "id", "member")
            ->addWhere("memberId", strval($memberId), "=")
            ->addOrderBy("teamNumber", true)
            ->addLimit(6);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $entity = new Team($line[$c[0]],
                    $line[$c[1]],
                    $line[$c[2]],
                    $line[$c[3]],
                    $line[$c[4]],
                    $line[$c[5]],
                    $line[$c[6]],
                    $line[$c[7]]
                );
                $entities[(int) $line[$c[2]]] = $entity;
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }

    public function addTeam($memberId, $teamNumber, $hero1Id, $hero2Id, $hero3Id, $hero4Id) {
        $this->reset();
        if (!$this->insert(Array("memberId", "teamNumber", "hero1Id", "hero2Id", "hero3Id", "hero4Id"),
                           Array($memberId, $teamNumber, $hero1Id, $hero2Id, $hero3Id, $hero4Id))) {
            throw new Exception($this->getError());
        }
        return true;
    }

    public function updateTeam($id, $hero1Id, $hero2Id, $hero3Id, $hero4Id) {
        try {
            $stmt = $this->_db->prepare("UPDATE team 
                                        SET hero1Id=:hero1IdQuery,
                                            hero2Id=:hero2IdQuery,
                                            hero3Id=:hero3IdQuery,
                                            hero4Id=:hero4IdQuery
                                        WHERE id LIKE :idQuery");
            $stmt->bindParam(':idQuery', $id, PDO::PARAM_INT);
            $stmt->bindParam(':hero1IdQuery', $hero1Id, PDO::PARAM_INT);
            $stmt->bindParam(':hero2IdQuery', $hero2Id, PDO::PARAM_INT);
            $stmt->bindParam(':hero3IdQuery', $hero3Id, PDO::PARAM_INT);
            $stmt->bindParam(':hero4IdQuery', $hero4Id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e){
            return array("error" => $e->getMessage());
        }
    }

    public function updateTeamNyMemberAndTeam($id,  array $params) {
        $this->reset();
        if(!$this->update($id, array_keys($params), array_values($params))) {
            throw new Exception($this->getError());
        }
        return true;
    }

    public function getTeamId($memberId, $teamNumber) {
        $this->reset();
        $this->addColumns(Array("id", "teamNumber"))
            ->addWhere("memberId", strval($memberId), "=")
            ->addWhere("teamNumber", strval($teamNumber), "=")
            ->addLimit(1);
        if($this->select()) {
            $c = $this->getColumns();
            $results = $this->getResult();
            return ((count($results) >= 1) ? (int) $results[0][$c[0]] : 0);
        } else {
            throw new Exception($this->getError());
        }
    }
}