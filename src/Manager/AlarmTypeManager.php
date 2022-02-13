<?php

namespace App\Manager;

class AlarmTypeManager extends AbstractManager
{
    const DB_NAME = 'alarmType';
    const DB_PREFIX = 'alt';

    protected function getTable() { return [AlarmTypeManager::DB_PREFIX, AlarmTypeManager::DB_NAME]; }
}