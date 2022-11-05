<?php

use Slim\App;
use Slim\Views\Twig;
use App\Controller as C;
use App\Middleware\UserAuthMiddleware as Auth;

return function (App $app) {
    $app->group('/v2', function ($group) {
// HOME pages
        $group->group('', function ($home) {
            $home->get('', [C\HomeController::class, 'index'])->setName('home')->setArgument('content-title', 'Home');
            $home->map(['GET', 'POST'], '/connect', [C\HomeController::class, 'connect'])->setName('connect')->setArgument('content-title', 'Connexion');
            $home->map(['GET', 'POST'], '/register', [C\HomeController::class, 'register'])->setName('register')->setArgument('content-title', 'Enregistrement');
            $home->get('/disconnect', [C\HomeController::class, 'disconnect'])->setName('disconnect');
            $home->post('/changeGuild', [C\HomeController::class, 'changeGuild'])->setName('change-guild');
        });
// errors pages
        $group->group('/error', function ($error) {
            $error->get('/403', [C\ErrorController::class, 'x403'])->setName('403');
        });
// profile pages
        $group->group('/profile', function ($profile) {
            $profile->map(['GET', 'POST'], '[/me]', [C\ProfileController::class, 'me'])->setName('my-profile');
            $profile->get('/{id}', [C\ProfileController::class, 'index'])->setName('profile');
            $profile->post('/{id}/settings', [C\ProfileController::class, 'settings'])->setName('profile-settings');
            $profile->post('/{id}/upgrade', [C\ProfileController::class, 'upgrade'])->setName('profile-heroes');
        })->add(Auth::class);
// boss-ailments pages
        $group->group('/boss', function ($boss) {
            $boss->get('', [C\BossController::class, 'index'])->setName('bosses')->setArgument('content-title', 'Liste des Boss');
            $boss->get('/{id}', [C\BossController::class, 'info'])->setName('boss');
            $boss->post('/{id}/ailment', [C\BossController::class, 'ailment'])->setName('boss-ailment');
        })->add(Auth::class);
// tips pages
        $group->group('/tip', function ($tip) {
            $tip->get('', fn ($request, $response) => $this->get(Twig::class)->render($response, 'tip/index.twig'))->setName('tip')->setArgument('content-title', 'Liens & Astuces');
        });
// conquest pages
        $group->group('/conquest', function ($tip) {
            $tip->get('/priority', fn ($request, $response) => $this->get(Twig::class)->render($response, 'conquest/priority.twig'))->setName('conquest-priority')->setArgument('content-title', 'Conquête en cours');
            $tip->get('/old', fn ($request, $response) => $this->get(Twig::class)->render($response, 'conquest/old.twig'))->setName('conquest-old')->setArgument('content-title', 'Stratégie de conquête');
            $tip->get('/tremens', fn ($request, $response) => $this->get(Twig::class)->render($response, 'conquest/tremens.twig'))->setName('conquest-tremens')->setArgument('content-title', 'Stratégie des Tremens');
            $tip->get('/nocturnum', fn ($request, $response) => $this->get(Twig::class)->render($response, 'conquest/nocturnum.twig'))->setName('conquest-nocturnum')->setArgument('content-title', 'Stratégie des Nocturnums');
        });
// alliance pages
        $group->group('/alliance', function ($alliance) {
            $alliance->get('', [C\AllianceController::class, 'index'])->setName('alliance')->setArgument('content-title', 'L\'alliance Délirium');
            $alliance->map(['GET', 'POST'], '/guild', [C\AllianceController::class, 'new'])->setName('alliance-new-guild')->setArgument('content-title', 'Nouvelle guilde')->add(Auth::class);
            $alliance->map(['GET', 'POST'], '/guild/{id}', [C\AllianceController::class, 'guild'])->setName('alliance-guild')->add(Auth::class);
        });
// ADMIN pages
        $group->group('/admin', function ($admin) {
            $admin->map(['GET', 'POST'], '/dashboard', [C\AdminController::class, 'todo'])->setName('admin-dashboard')->setArgument('content-title', 'TODO');
            $admin->map(['GET', 'POST'], '/heroes', [C\AdminController::class, 'heroes'])->setName('admin-heroes')->setArgument('content-title', 'Liste des Héros et des armes');
            $admin->get('/heroes/{id}/delete', [C\AdminController::class, 'delhero'])->setName('admin-hero-delete');
            $admin->map(['GET', 'POST'], '/bosses', [C\AdminController::class, 'todo'])->setName('admin-bosses')->setArgument('content-title', 'TODO');
            $admin->map(['GET', 'POST'], '/members', [C\AdminController::class, 'todo'])->setName('admin-members')->setArgument('content-title', 'TODO');
            $admin->map(['GET', 'POST'], '/worlds', [C\AdminController::class, 'worlds'])->setName('admin-worlds')->setArgument('content-title', 'Liste des mondes');
            $admin->get('/worlds/{id}/delete', [C\AdminController::class, 'delWorld'])->setName('admin-world-delete');
            $admin->map(['GET', 'POST'], '/raids', [C\AdminController::class, 'raids'])->setName('admin-raids')->setArgument('content-title', 'Liste des raids');
            $admin->get('/raids/{id}/delete', [C\AdminController::class, 'delraid'])->setName('admin-raid-delete');
        })->add(Auth::class);
// MEMBER page
        $group->group('/members', function ($members) {
            $members->map(['GET', 'POST'], '/new', [C\MemberController::class, 'new'])->setName('member-new')->setArgument('content-title', 'Nouveau membre');
            $members->map(['GET', 'POST'], '/{id}/edit', [C\MemberController::class, 'edit'])->setName('member-edit');
            $members->post('/{id}/delete', [C\MemberController::class, 'delete'])->setName('member-delete');
            $members->map(['GET', 'POST'], '/{id}/team', [C\MemberController::class, 'team'])->setName('member-team')->setArgument('content-title', 'Liste des teams');
            $members->map(['GET', 'POST'], '/pending', [C\MemberController::class, 'pending'])->setName('member-pending')->setArgument('content-title', 'Associer un compte à un joueur');
            $members->get( '/alliance', [C\MemberController::class, 'alliance'])->setName('member-alliance')->setArgument('content-title', 'Listes des Membres');
            $members->get('/{id}/crew', [C\MemberController::class, 'crew'])->setName('member-crew');
            $members->map(['GET', 'POST'], '/{id}/crew/edit', [C\MemberController::class, 'crewedit'])->setName('member-crew-edit');
        });
// RAID page
        $group->group('/raid', function ($raid) {
            $raid->map(['GET', 'POST'], '/info', [C\RaidController::class, 'info'])->setName('raid-info')->setArgument('content-title', 'Informations raid');
            $raid->post('/info/add', [C\RaidController::class, 'addInfo'])->setName('raid-info-add');
            $raid->get('/rank', [C\RaidController::class, 'rank'])->setName('raid-rank')->setArgument('content-title', 'Classement dans l\'alliance');
            $raid->get('/meteo[/{guildId}]', [C\RaidController::class, 'meteo'])->setName('raid-meteo');
            $raid->get('/followup[/{guildId}]', [C\RaidController::class, 'followup'])->setName('raid-followup');
            $raid->get('/miss[/{guildId}]', [C\RaidController::class, 'miss'])->setName('raid-miss');
            $raid->get('/summary', [C\RaidController::class, 'summary'])->setName('raid-summary')->setArgument('content-title', 'Liste des 180 dernières attaques');
            //$raid->map(['GET', 'POST'], '/fights/old', [C\RaidController::class, 'oldfights'])->setName('raid-old-fights')->setArgument('content-title', 'X');
            $raid->map(['GET', 'POST'], '/fights[/{guildId}]', [C\RaidController::class, 'fights'])->setName('raid-fights');
            $raid->get('/fights/{guildId}/end', [C\RaidController::class, 'fightsEnd'])->setName('raid-fights-end');
            $raid->post('/fights/{guildId}/end', [C\RaidController::class, 'fightsEndSave'])->setName('raid-fights-end-save');
            $raid->map(['GET', 'POST'], '/finalise[/{guildId}]', [C\RaidController::class, 'finalise'])->setName('raid-finalise');
        })->add(Auth::class);
    });
};