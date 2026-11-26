<?php

use Kirby\Blueprint\Normalizer;
use Kirby\Cms\App;
use Kirby\Panel\Panel;
use SylvainJule\Bouncer\BouncerNavField;
use SylvainJule\Bouncer\ChangesController;
use SylvainJule\Bouncer\FilesController;
use SylvainJule\Bouncer\PagesController;

require_once __DIR__ . '/lib/bouncer.php';
require_once __DIR__ . '/lib/field.php';
require_once __DIR__ . '/lib/controllers.php';

Normalizer::$sectionFields['bouncernav'] = 'bouncernav';

App::plugin('sylvainjule/bouncer', [
    'options' => [
        'list' => []
    ],
    'fields' => [
        'bouncernav' => BouncerNavField::class
    ],
    'areas' => [
        'site' => function ($kirby) {
            return [
                'searches' => [
                    'pages' => ['action' => PagesController::class],
                    'files' => ['action' => FilesController::class]
                ],
                'dialogs' => [
                    'changes' => ['action' => ChangesController::class]
                ]
            ];
        }
    ],
    'hooks' => [
        'panel.route:before' => function ($route, $path, $method) {
            $kirby = kirby();
            $user  = $kirby->user();
            if(!$user || !$path) {
                return $route;
            }

            $attributes = $route->attributes();
            if(($attributes['type'] ?? 'view') !== 'view' || ($attributes['auth'] ?? true) === false) {
                return $route;
            }

            $fallback = Bouncer::redirectPath($user, $path);
            if($fallback !== null) {
                Panel::go($fallback);
            }
            
            return $route;
        }
    ],
    'api' => [
        'routes' => function ($kirby) {
            return [
                [
                    'pattern' => 'current-user',
                    'action' => fn() => Bouncer::currentUser($kirby->user())
                ]
            ];
        }
    ],
    'translations' => [
        'en' => ['sylvainjule.bouncer.switch' => 'Switch to'],
        'de' => ['sylvainjule.bouncer.switch' => 'Wechseln zu'],
        'fr' => ['sylvainjule.bouncer.switch' => 'Basculer vers']
    ]
]);
