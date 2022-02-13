<?php

namespace App\Manager;

use Exception;
use App\Entity\Weapon;
use App\Entity\WeaponRaid;

class WeaponManager extends AbstractManager
{
    const DB_NAME = 'weapon';
    const DB_PREFIX = 'wpn';

    protected function getTable() { return [WeaponManager::DB_PREFIX, WeaponManager::DB_NAME]; }
    
    public function getAll() {
        $this->reset();
        $this->addColumns(Array("id", "name", "ailmentId", "charactId"))
             ->addOrderBy("name", true);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            foreach ($results as $line) {
                $entities[(int) $line[$c[0]]] = new Weapon(
                        $line[$c[0]],
                        $line[$c[1]],
                        $line[$c[2]],
                        $line[$c[3]],
                        );
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function getByAilmentBoss($ailmentId, $bossId) {
        $this->reset();
        $str = "SELECT weapon.id as wpn_id, weapon.name as wpn_name, weapon.ailmentId as wpn_ailmentId, weapon.charactId as wpn_charactId,
charact.name as chr_name,
ate_id, ate_bossId, ate_rate
FROM weapon
LEFT JOIN charact
ON weapon.charactId = charact.id
LEFT JOIN (
    SELECT ailmentendurance.id as ate_id,
           ailmentendurance.weaponId as ate_weaponId,
           ailmentendurance.bossId as ate_bossId,
           ailmentendurance.rate as ate_rate
    FROM ailmentendurance
    WHERE ailmentendurance.bossId = '$bossId'
) ailmentendurance
ON weapon.id = ate_weaponId
WHERE weapon.ailmentId = '$ailmentId' ORDER BY weapon.name ASC";
        if($this->excuseCustomQuery($str)) {
            $results = $this->getResult();
            foreach ($results as $line) {
                $entity = new Weapon($line["wpn_id"],
                        $line["wpn_name"],
                        $line["wpn_ailmentId"],
                        $line["wpn_charactId"],
                        $line["ate_id"],
                        $line["ate_bossId"],
                        $line["ate_rate"],
                        $line["chr_name"]
                        );
                $entities[(int) $line["wpn_id"]] = $entity;
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
    
    public function getByAilmentBossRaid($ailmentId, $bossId1, $bossId2, $bossId3, $bossId4) {
        $this->reset();
        $str = "SELECT weapon.id as wpn_id, weapon.name as wpn_name, weapon.ailmentId as wpn_ailmentId, weapon.charactId as wpn_charactId,
charact.name as chr_name,
ate_bossId1, ate_rate1, ate_bossId2, ate_rate2, ate_bossId3, ate_rate3, ate_bossId4, ate_rate4
FROM weapon
LEFT JOIN charact
ON weapon.charactId = charact.id
LEFT JOIN (
    SELECT ailmentendurance.weaponId as ate_weaponId1,
           ailmentendurance.bossId as ate_bossId1,
           ailmentendurance.rate as ate_rate1
    FROM ailmentendurance
    WHERE ailmentendurance.bossId = '$bossId1'
) ailmentendurance1
ON weapon.id = ate_weaponId1
LEFT JOIN (
    SELECT ailmentendurance.weaponId as ate_weaponId2,
           ailmentendurance.bossId as ate_bossId2,
           ailmentendurance.rate as ate_rate2
    FROM ailmentendurance
    WHERE ailmentendurance.bossId = '$bossId2'
) ailmentendurance2
ON weapon.id = ate_weaponId2
LEFT JOIN (
    SELECT ailmentendurance.weaponId as ate_weaponId3,
           ailmentendurance.bossId as ate_bossId3,
           ailmentendurance.rate as ate_rate3
    FROM ailmentendurance
    WHERE ailmentendurance.bossId = '$bossId3'
) ailmentendurance3
ON weapon.id = ate_weaponId3
LEFT JOIN (
    SELECT ailmentendurance.weaponId as ate_weaponId4,
           ailmentendurance.bossId as ate_bossId4,
           ailmentendurance.rate as ate_rate4
    FROM ailmentendurance
    WHERE ailmentendurance.bossId = '$bossId4'
) ailmentendurance4
ON weapon.id = ate_weaponId4
WHERE weapon.ailmentId = '$ailmentId' ORDER BY weapon.name ASC";
        if($this->excuseCustomQuery($str)) {
            $results = $this->getResult();
            foreach ($results as $line) {
                $entity = new WeaponRaid($line["wpn_id"],
                        $line["wpn_name"],
                        $line["wpn_ailmentId"],
                        $line["wpn_charactId"],
                        $line["ate_bossId1"],
                        $line["ate_rate1"],
                        $line["ate_bossId2"],
                        $line["ate_rate2"],
                        $line["ate_bossId3"],
                        $line["ate_rate3"],
                        $line["ate_bossId4"],
                        $line["ate_rate4"],
                        $line["chr_name"]
                        );
                $entities[(int) $line["wpn_id"]] = $entity;
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
}