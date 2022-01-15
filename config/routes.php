<?php

use Slim\App;

return function (App $app) {
    $app->group('/slim', function ($group) {
        $group->get('', \App\Action\HomeAction::class)->setName('home')->setArgument('content-title', 'Home');
    });
};