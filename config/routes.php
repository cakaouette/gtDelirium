<?php

use Slim\App;
use App\Controller as C;
use App\Middleware\UserAuthMiddleware as Auth;

return function (App $app) {
    $app->group('/slim', function ($group) {
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
    });
};