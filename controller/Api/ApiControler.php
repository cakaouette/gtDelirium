<?php
include_once('tools/utils.php');

include_once('controller/AbstractControler.php');

include_once('model/Manager/AlarmManager.php');
include_once('model/Manager/MemberManager.php');

class ApiControler extends AbstractControler
{
    const WEEKHOOK_TEST = "https://discord.com/api/webhooks/871385825278050305/LtTKggMP4fYY-0qqLMzKLh14C6eL0kMsZDePommDCHXgEnrIRMrxvIoUSatK7gSJIkMH";
    const WEEBHOOK_Raid = "https://discord.com/api/webhooks/871397876205510697/n7PSLbfJVMLiXbTLegNyFlsFdaVK5_rl4NA-30PSNun2gDilpnwlveWvWQUpdmVqSA-y";
    const WEEBHOOK_Coop = "https://discord.com/api/webhooks/925342408219177020/m60eOMidDS3w6m0Uo39fsKDS3f8zX99SsSiEmGiyTxRRcPLisdwQ3qL4n-ayVH_am-5Z";
    const WEEBHOOK_Arena = "https://discord.com/api/webhooks/925342101238059088/A6W-rkbjTgxStZrd2ee7w0awgCdNfmvSblRJMgC-7k4cC-R18tMY1XN7_REuU4iblino";
    const WEEBHOOK_MasterArena = ApiControler::WEEBHOOK_Arena;
    const WEEBHOOK_TeamRaid = "https://discord.com/api/webhooks/917671890867331163/TRZT_mturl4QSkIfwXiBzNwWG3n2mbmgTbjcblfvuBhmGynkvqFFK7nu3m0AqSNSJRCT";
    
    public function __construct() {
        parent::__construct("Discord");
        $this->_weebhook = Array(
            "Test" => ApiControler::WEEKHOOK_TEST,
            "Raid" => ApiControler::WEEBHOOK_Raid,
            "Coop" => ApiControler::WEEBHOOK_Coop,
            "Arena" => ApiControler::WEEBHOOK_Raid,
            "MasterArena" => ApiControler::WEEBHOOK_Arena,
            "Perso" => ApiControler::WEEBHOOK_MasterArena,
            "TeamRaid" => ApiControler::WEEBHOOK_TeamRaid,
        );
    }
    
    private function isMute($alarmName, $repetition, $muteDate) {
        // ----- MUTE -----
        if (!is_null($muteDate) and date("Y-m-d") <= date($muteDate)) {
            return true;
        }
        
        // ----- RAID -----
        if ($alarmName == "Raid" and getVar($_SESSION, "raidInfo")["isFinished"] == 1) {
            return true;
        }
        
        // ----- REPEAT -----
        $dNum = date('N'); $wNum = date('W');
        $dNum = $dNum + (($wNum % 2) == 0 ? 7 : 0);
        $this->addMsg("info", "week number= $wNum day number= $dNum");
        return !in_array($dNum, $repetition);
    }

    public function sendAlarm() {
        $alarmManager = new AlarmManager();
        try {
            $alarms = $alarmManager->getByTime(date('H'), intdiv(date('i'), 30)*30);
        } catch (Exception $e) {
            $alarms = [];
            $this->addMsg("info", $e->getMessage());
        }
        foreach ($alarms as $unused => $alarm) {
            if ($this->isMute($alarm->getAlarmInfo()["name"],
                    $alarm->getAlarmInfo()["repetition"],
                    $alarm->getAlarmInfo()["muteDate"]))
            {
              $this->addMsg("success", "DEBUG: alarm is muted: message= ".$alarm->getMessages()["second"]);
              continue;
            }
            $message = $alarm->getAlarmInfo()["name"]." - ".$alarm->getMessages()["first"]
                    ." <@".$alarm->getMemberInfo()["discordId"]."> "
                    .$alarm->getMessages()["second"];
            $data = ['content' => $message];
            $options = [
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => json_encode($data)
                ]
            ];

            $context = stream_context_create($options);
            $weebhook = $this->_weebhook[$alarm->getAlarmInfo()["name"]];//$this->_weebhook[$alarm->getAlarmInfo()["name"]];$this->_weebhook["Test"];
            $result = file_get_contents($weebhook, false, $context);
            $this->addMsg("info", "DEBUG: $result");
            if (!($_SESSION["debug"] ?? false)) {
                sleep(2 /*secondes*/);
            }
        }
    }

    public function sendTeamRaid($id, $msg) {
        $memberManager = new MemberManager();
        try {
            $info = $memberManager->getDiscordId($id);
            $info = "<@".$info["discordId"].">";
        } catch (Exception $e) {
            try {
                $info = $memberManager->getById($id);
                $info = $info->getName();
            } catch (Exception $e) {
                $info = "Quelqu'un ";
            }
            $this->addMsg("info", $e->getMessage());
        }
        $message = $info." a besoin d'aide pour ses teams http://gt.lacakaouette.fr/index.php?page=member&subpage=crew&id=$id";
        $data = ['content' => $message];
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $weebhook = $this->_weebhook["TeamRaid"];
        $result = file_get_contents($weebhook, false, $context);
        header('Location: index.php?page=member&subpage=crew&id='.$id."&view=forced");
    }
}
