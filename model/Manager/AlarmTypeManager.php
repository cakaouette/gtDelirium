<?php
include_once("AbstractManager.php");

class AlarmTypeManager extends AbstractManager
{
    const DB_NAME = 'alarmType';
    const DB_PREFIX = 'alt';

    public function __construct() {
        parent::__construct(AlarmTypeManager::DB_NAME, AlarmTypeManager::DB_PREFIX);
    }
}