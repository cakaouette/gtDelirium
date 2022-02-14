<?php

namespace App\Entity;

class Alarm
{
    private $_id;
    private $_alarmTypeId;
    private $_alarmTypeName;
    private $_hour;
    private $_repetition;
    private $_memberId;
    private $_discordId;
    private $_message;
    private $_secMsg;
    private $_muteDate;

    public function __construct($id,
            $alarmTypeId, $alarmTypeName, $repetition, $hour,
            $memberId, $discordId,
            $message, $addMsg, $muteDate) {
        $this->_id = $id;
        $this->_alarmTypeId = $alarmTypeId;
        $this->_alarmTypeName = $alarmTypeName;
        $this->_hour = $hour;
        $this->_repetition = explode(",", $repetition);
        $this->_memberId = $memberId;
        $this->_discordId = $discordId;
        $this->_message = $message;
        $this->_secMsg = $addMsg;
        $this->_muteDate = $muteDate;
    }

    public function getId() { return $this->_id; }
    public function getAlarmInfo() {
      return Array("id" => $this->_alarmTypeId,
                   "name" => $this->_alarmTypeName,
                   "repetition" => $this->_repetition,
                   "hour" => $this->_hour,
                   "muteDate" => $this->_muteDate);
    }
    public function getMemberInfo() { return Array("id" => $this->_memberId, "discordId" => $this->_discordId); }
    public function getMessages() { return Array("first" => $this->_message, "second" => $this->_secMsg); }
}