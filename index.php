<?php

require_once __DIR__ . '/lib/bouncer.php';

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

Kirby::plugin('sylvainjule/bouncer', [
    'options'  => [
        'list'       => []
    ],
    'sections' => [
        'bouncernav' => []
    ],
    'hooks'    => [
        'panel.route:before' => function($route, $path, $method) {
            $user  = kirby()->user();
            if(!$user || !$path) return;

            if (str_starts_with($path, "dialogs")
                || str_starts_with($path, "dropdowns")
                || str_starts_with($path, "search")) {
                return;
            }

            $currentRole = $user->role()->name();

            foreach(option('sylvainjule.bouncer.list') as $role => $options) {
                if($currentRole == $role) {
                    $fieldname    = $options['fieldname'];
                    $allowed      = Bouncer::getAllowedPages($user, $fieldname, true);
                    $allowedPaths = A::pluck($allowed, 'path');
                    $currentPath  = '/'. $path;

                    if(!in_array($currentPath, $allowedPaths)) {
                        Panel::go($allowedPaths[0]);
                    }
                }
            }
        }
    ],
    'api'      => [
        'routes' => function ($kirby) {
            return [
                [
                    'pattern' => 'current-user',
                    'action'  => function() use ($kirby) {
                        $currentUser = $kirby->user();
                        $currentRole = $currentUser->role()->name();
                        $allowed     = [];
                        $nav         = false;

                        foreach(option('sylvainjule.bouncer.list') as $role => $options) {
                            if($currentRole == $role) {
                                $fieldname = $options['fieldname'];

                                $allowed   = Bouncer::getAllowedPages($currentUser, $fieldname);
                                $nav       = array_key_exists('nav', $options) && $options['nav'] ? $options['nav'] : false;
                            }
                        }

                        return array(
                            'nav'     => $nav,
                            'allowed' => $allowed,
                        );
                    }
                ]
            ];
        }
    ]
]);
