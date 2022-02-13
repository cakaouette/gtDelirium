<?php

namespace App\Manager;

use Exception;
use App\Entity\Alarm;

class AlarmManager extends AbstractManager
{
    const DB_NAME = 'alarm';
    const DB_PREFIX = 'alm';

    protected function getTable() { return [AlarmManager::DB_PREFIX, AlarmManager::DB_NAME]; }
    
    public function getByTime($hour, $min) {
        $this->reset();
        $this->addColumns(Array("id", "alarmTypeId", "memberId", "hour", "repetition", "message", "muteDate"))
             ->addColumns(Array("name", "defaultRepetition", "message"), false, AlarmTypeManager::DB_NAME, AlarmTypeManager::DB_PREFIX)
             ->addColumns(Array("discordId"), false, MemberManager::DB_NAME, MemberManager::DB_PREFIX)
             ->addWhere("hour", date("H:i:s", mktime($hour, $min, 0)), ">=")
             ->addWhere("hour", date("H:i:s", mktime($hour, $min+29, 59)), "<=")
             ->addJoin("LEFT", "alarmTypeId", "id", AlarmTypeManager::DB_NAME)
             ->addJoin("LEFT", "memberId", "id", MemberManager::DB_NAME);
        if($this->select()) {
            $c = $this->getColumns();
            $entities = Array();
            $results = $this->getResult();
            if (empty($results)) {
                throw new Exception("Aucune alarme trouvée");
            }
            foreach ($results as $line) {
                $entities[] = new Alarm(
                        $line[$c[0]], //id
                        $line[$c[1]], //alarmTypeId
                        $line[$c[7]], //typeName
                        $line[$c[4]] ?? $line[$c[8]], //repetition
                        $line[$c[3]], //hour
                        $line[$c[2]], //memberId
                        $line[$c[10]], //discordId
                        $line[$c[9]], //msg
                        $line[$c[5]], //secMsg
                        $line[$c[6]], //muteDate
                        );
            }
            return $entities;
        } else {
            throw new Exception($this->getError());
        }
    }
}
