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
                    'path'  => $page->panel()->url(true)
                ];

                $children = $extra ? static::getChildren($page) : [];
                $allowed  = array_merge($allowed, $children);
            }
        }

        if($extra) {
            $allowed[] = [
                'title' => 'Account',
                'path'  => '/account'
            ];
            $allowed[] = [
                'title' => 'Login',
                'path'  => '/login'
            ];
            $allowed[] = [
                'title' => 'Logout',
                'path'  => '/logout'
            ];
            $allowed[] = [
                'title' => 'Reset password',
                'path'  => '/reset-password'
            ];
        }

        return $allowed;
    }
    
    private static function getChildrenFiles(Kirby\Cms\Page $page) {
        if (!($page->hasFiles())) { return []; }
        
        $allowed = [];
        $files   = $page->files();
        foreach($files as $f) {
            $allowed[] = [
                'title' => $f->title()->value(),
                'path'  => $f->panel()->url(true)
            ];
        }

        return $allowed;
    }

    private static function getChildren(Kirby\Cms\Page $page) {
        if (!($page->hasChildren() || $page->hasDrafts() || $page->hasFiles())) { return []; }

        $allowed = [];
        $pages   = $page->childrenAndDrafts();
        
        if($page->hasFiles()){
            $files = static::getChildrenFiles($page);
            $allowed  = array_merge($allowed, $files);
        }

        foreach($pages as $p) {
            $allowed[] = [
                'title' => $p->title()->value(),
                'path'  => $p->panel()->url(true)
            ];

            $children = static::getChildren($p);
            $allowed  = array_merge($allowed, $children);
        }

        return $allowed;
    }

}
