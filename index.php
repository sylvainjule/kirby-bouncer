<?php

Kirby::plugin('sylvainjule/bouncer', [
    'options' => [
        'list' => []
    ],
    'sections' => [
        'bouncernav' => []
    ],
    'api' => [
        'routes' => function ($kirby) {
            return [
                [
                    'pattern' => 'current-user',
                    'action'  => function() use ($kirby) {
                        $currentUser = $kirby->user();
                        $currentRole = $currentUser->role()->name();
                        $restriction = [];
                        $nav         = false;

                        foreach(option('sylvainjule.bouncer.list') as $role => $options) {
                            if($currentRole == $role) {
                                $fieldname = $options['fieldname'];
                                $pages     = $currentUser->$fieldname()->toPages();
                                $nav       = array_key_exists('nav', $options) && $options['nav'] ? $options['nav'] : false;

                                if($pages->count()) {
                                    foreach($pages as $page) {
                                        $restriction[] = [
                                            'title' => $page->title()->value(),
                                            'path'  => $page->panelUrl(true)
                                        ];
                                    }
                                }
                                else {
                                    $restriction[] = [
                                        'title' => 'Account',
                                        'path'  => '/account'
                                    ];
                                }
                            }
                        }

                        return array(
                            'nav'         => $nav,
                            'restriction' => $restriction,
                        );
                    }
                ]
            ];
        }
    ]
]);
