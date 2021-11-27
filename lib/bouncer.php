<?php

class Bouncer {

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
