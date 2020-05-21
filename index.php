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
                        $restriction = [];

                        foreach(option('sylvainjule.bouncer.list') as $role => $fieldname) {
                            if($currentRole == $role) {
                                $pages = $currentUser->$fieldname()->toPages();

                                if($pages->count()) {
                                    foreach($pages as $page) {
                                        $restriction[] = $page->panelUrl(true);
                                    }
                                }
                                else {
                                    $restriction[] = '/account';
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
