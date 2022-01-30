<?php

use Slim\App;
use Slim\Views\Twig;
use App\Controller as C;
use App\Middleware\UserAuthMiddleware as Auth;

return function (App $app) {
    $app->group('/v2', function ($group) {
        $group->group('', function ($home) {
            $home->get('', [C\HomeController::class, 'index'])->setName('home')->setArgument('content-title', 'Home');
            $home->map(['GET', 'POST'], '/connect', [C\HomeController::class, 'connect'])->setName('connect')->setArgument('content-title', 'Connexion');
            $home->map(['GET', 'POST'], '/register', [C\HomeController::class, 'register'])->setName('register')->setArgument('content-title', 'Enregistrement');
            $home->get('/disconnect', [C\HomeController::class, 'disconnect'])->setName('disconnect');
        });
        $group->group('/error', function ($error) {
            $error->get('/403', [C\ErrorController::class, 'x403'])->setName('403');
        });
        $group->group('/profile', function ($profile) {
            $profile->get('[/me]', [C\ProfileController::class, 'me'])->setName('my-profile');
            $profile->get('/{id}', [C\ProfileController::class, 'index'])->setName('profile');
            $profile->post('/{id}/settings', [C\ProfileController::class, 'settings'])->setName('profile-settings');
        })->add(Auth::class);
        $group->group('/boss', function ($boss) {
            $boss->get('', [C\BossController::class, 'index'])->setName('bosses')->setArgument('content-title', 'Liste des Boss');
            $boss->get('/{id}', [C\BossController::class, 'info'])->setName('boss');
            $boss->post('/{id}/ailment', [C\BossController::class, 'ailment'])->setName('boss-ailment');
        })->add(Auth::class);
        $group->group('/tip', function ($tip) {
            $tip->get('', fn ($request, $response) => $this->get(Twig::class)->render($response, 'tip/index.twig'))->setName('tip')->setArgument('content-title', 'Liens & Astuces');
        })->add(Auth::class);
        $group->group('/conquest', function ($tip) {
            $tip->get('/priority', fn ($request, $response) => $this->get(Twig::class)->render($response, 'conquest/priority.twig'))->setName('conquest-priority')->setArgument('content-title', 'Conquête en cours');
            $tip->get('/old', fn ($request, $response) => $this->get(Twig::class)->render($response, 'conquest/old.twig'))->setName('conquest-old')->setArgument('content-title', 'Stratégie de conquête');
            $tip->get('/tremens', fn ($request, $response) => $this->get(Twig::class)->render($response, 'conquest/tremens.twig'))->setName('conquest-tremens')->setArgument('content-title', 'Stratégie des Tremens');
            $tip->get('/nocturnum', fn ($request, $response) => $this->get(Twig::class)->render($response, 'conquest/nocturnum.twig'))->setName('conquest-nocturnum')->setArgument('content-title', 'Stratégie des Nocturnums');
        })->add(Auth::class);
        $group->group('/alliance', function ($home) {
            $home->get('', [C\AllianceController::class, 'index'])->setName('alliance')->setArgument('content-title', 'L\'alliance Délirium');
            $home->map(['GET', 'POST'], '/guild', [C\AllianceController::class, 'new'])->setName('alliance-new-guild')->setArgument('content-title', 'Nouvelle guilde');
            $home->map(['GET', 'POST'], '/guild/{id}', [C\AllianceController::class, 'guild'])->setName('alliance-guild');
        });
    });
};