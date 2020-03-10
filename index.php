<?php

Kirby::plugin('sylvainjule/bouncer', [
    'options' => array(
        'list' => []
    ),
    'api' => [
        'routes' => function ($kirby) {
            return [
                [
                    'pattern' => 'current-user',
                    'action'  => function() use ($kirby) {
                        $currentUser = $kirby->user();
                        $currentRole = $currentUser->role()->name();
                        $restriction = false;

                        foreach(option('sylvainjule.bouncer.list') as $role => $fieldname) {
                            if($currentRole == $role) {
                                if($p = $currentUser->$fieldname()->toPage()) {
                                    $restriction = array(
                                        'path' => $p->panelUrl(true),
                                    );
                                }
                                else {
                                    $restriction = array(
                                        'path' => '/users/'. $currentUser->id(),
                                    );
                                }
                            }
                        }

                        return array(
                            'restriction' => $restriction,
                        );
                    }
                ]
            ];
        }
    ]
]);