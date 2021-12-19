<?php

class Bouncer {

    private static function getChildren($allowed, Page $page) {
        if (!$page->hasChildren()) {
            return [];
        }

        $allowed = [];
        $pages = $page->childrenAndDrafts();
        foreach($pages as $p) {
            $allowed[] = [
                'title' => $p->title()->value(),
                'path'  => $p->panelUrl(true)
            ];


            $children = Bouncer::getChildren($allowed, $p);
            $allowed = array_merge($allowed, $children);
        }

        return $allowed;
    }

    public static function getAllowedPages($user, $fieldname, $extra = false) {
        $kirby   = kirby();
        $allowed = [];
        $pages   = $user->$fieldname()->yaml();
        $pages   = array_map(function($p) use($kirby) { return $kirby->page($p); }, $pages);
        $pages   = new Pages($pages);

        if($pages->count()) {
            foreach($pages as $page) {
                $allowed[] = [
                    'title' => $page->title()->value(),
                    'path'  => $page->panelUrl(true)
                ];

                $children = $extra ? Bouncer::getChildren($allowed, $page) : [];
                $allowed = array_merge($allowed, $children);
            }
        }

        if($extra) {
            $allowed[] = [
                'title' => 'Account',
                'path'  => '/account'
            ];
            $allowed[] = [
                'title' => 'Logout',
                'path'  => '/logout'
            ];
        }

        return $allowed;
    }

}
