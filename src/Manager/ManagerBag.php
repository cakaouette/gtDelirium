<?php 

namespace App\Manager;

//This class is used to avoid always importing required classes in parent constructors.
//There may be a better way with php-di. I haven't searched for it.
//For example, we have BaseController imports Twig, SessionInterface and RouteParserInterface.
//These imports are not necessary needed in classes that inherit for BaseController,
//but if we want to add other injectable parameters to the constructor
//we need to import them for php-di to work (it needs types specified in the child's constructor)
final class ManagerBag {

    private array $managers;

    public function  __construct(
            PermissionManager $permission,
            GuildManager $guild,
            MemberManager $member,
            TeamManager $team,
            AlarmManager $alarm,
            AlarmTypeManager $alarmType,
            FightManager $fight,
            BossManager $boss,
            BossInfoManager $bossInfo,
            BossStrategyManager $bossStrategy,
            CharacterManager $character,
            ElementManager $element,
            AilmentEnduranceManager $ailmentEndurance,
            AilmentManager $ailment,
            CrewManager $crew,
            PendingManager $pending,
            RaidManager $raid,
            RaidInfoManager $raidInfo,
            RankGoalManager $rankGoal,
            RankManager $rank,
            WeaponManager $weapon,
            SettingManager $setting,
            WorldManager $world)
      {
        $this->managers = [
            AilmentEnduranceManager::class => $ailmentEndurance,
            PermissionManager::class => $permission,
            AlarmTypeManager::class => $alarmType,
            CharacterManager::class => $character,
            RankGoalManager::class => $rankGoal,
            ElementManager::class => $element,
            AilmentManager::class => $ailment,
            PendingManager::class => $pending,
            MemberManager::class => $member,
            WeaponManager::class => $weapon,
            GuildManager::class => $guild,
            AlarmManager::class => $alarm,
            FightManager::class => $fight,
            TeamManager::class => $team,
            BossManager::class => $boss,
            BossInfoManager::class => $bossInfo,
            BossStrategyManager::class => $bossStrategy,
            CrewManager::class => $crew,
            RaidManager::class => $raid,
            RaidInfoManager::class => $raidInfo,
            RankManager::class => $rank,
            SettingManager::class => $setting,
            WorldManager::class => $world,
        ];
    }

    public function get(string $manager) {
        return $this->managers[$manager];
    }
}