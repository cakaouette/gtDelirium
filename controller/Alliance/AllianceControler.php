<?php
include_once('controller/AbstractControler.php');

include_once('model/Manager/GuildManager.php');
include_once('model/Entity/Guild.php');

include_once('model/Manager/RaidManager.php');
include_once('model/Manager/RankManager.php');
include_once('model/Manager/RankGoalManager.php');
include_once('model/Manager/MemberManager.php');

class AllianceControler extends AbstractControler
{
    private GuildManager $_guildManager;
    
    public function __construct() {
        parent::__construct("Alliance");
        $this->_guildManager = new GuildManager();
    }

    public function listGuildInfo() {
        $guilds =$this->_guildManager->getAll();
        $v_info = Array();
        try {
            $counts = (new MemberManager())->countMemberByGuildId();
            $ranks = (new RankManager())->getAll();
            $objectives = (new RankGoalManager())->getAll();
            $raids = (new RaidManager())->getDates();
        } catch (Exception $e) {
            $guilds = [];
            $this->addMsg("warning", "erreur interne");
        }
        foreach ($guilds as $guildId => $guild) {
            $v_info[$guildId] = Array(
                "guild" => $guild,
                "memberNumber" => $counts[$guildId],
                "ranks" => $ranks[$guildId],
                "lastRank" => end($ranks[$guildId])["rank"]
            );
        }
        foreach ($objectives as $guildId => $objective) {
            $v_info[$guildId]["objectives"] = $objective;
        }
        $v_raids = [];
        foreach ($raids as $raid) {
            $v_raids[$raid->getId()] = strtotime($raid->getDate());
        }
        ksort($v_raids);

        $v_content_header = "L'alliance Délirium";
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/AlliancePage/AllianceInfoView.php');
    }

    public function guildFormView($id) {
        if (is_null($id)) {
            $t = "Nouvelle guilde";
            $f_guild = new Guild(0, "", "");
        } else {
            $f_guild = $this->_guildManager->getById($id);
            $t = "Modification de ".$f_guild->getName();
        }
        $v_content_header = $t;
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/AlliancePage/AllianceGuildFormView.php');
    }
    
    public function addGuild($name, $color) {
        try {
            if ($this->_guildManager->add($name, $color)) {
                $this->addMsg("success", "Guilde ajoutée");
            }
        } catch (Exception $e) {
            $this->addMsg("danger", $e->getMessage());
        }
    }

}
