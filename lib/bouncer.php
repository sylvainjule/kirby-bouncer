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

            $allowed = array_merge($allowed, $kirby->option('sylvainjule.bouncer.list.'. $user->role() .'.extra', []));
        }

        return $allowed;
    }
    
    public static function isMovableTo($page, $parent) {
        try {
            $movable   = true;
            $user      = kirby()->user();
            $role      = $user->role()->name();
            $rolesList = option('sylvainjule.bouncer.list');

            if(array_key_exists($role, $rolesList) && array_key_exists('fieldname', $rolesList[$role])) {
                $allowed = static::getAllowedPages($user, $rolesList[$role]['fieldname']);
                $movable = in_array($parent->panel()->url(true), array_column($allowed, 'path'));
            }

            Kirby\Cms\PageRules::move($page, $parent);
            return $movable;
        } 
        catch (Throwable) {
            return false;
        }
    }

    public static function panelSearch($kirby, $query, $limit, $page) {
        $user      = $kirby->user();
        $role      = $user->role()->name();
        $rolesList = option('sylvainjule.bouncer.list');

        if(array_key_exists($role, $rolesList) && array_key_exists('fieldname', $rolesList[$role])) {
            $allowed = static::getAllowedPages(kirby()->user(), $rolesList[$role]['fieldname']);

            $pages = $kirby->site()->index(true);
            $pages = $pages->filter(function($p) use($allowed) {
                return in_array($p->panel()->url(true), array_column($allowed, 'path'));
            });   
            $pages = $pages->search($query)->filter('isListable', true);

            if ($limit !== null) {
                $pages = $pages->paginate($limit, $page);
            }

            return [
                'results' => $pages->values(fn ($page) => [
                    'image' => $page->panel()->image(),
                    'text' => Escape::html($page->title()->value()),
                    'link' => $page->panel()->url(true),
                    'info' => Escape::html($page->id()),
                    'uuid' => $page->uuid()?->toString(),
                ]),
                'pagination' => $pages->pagination()?->toArray()
            ];
        }
        else {
            return $kirby->core()->area('site')['searches']['pages']['query']($query, $limit, $page);
        }
    }

    public static function panelChanges() {
        $user      = kirby()->user();
        $role      = $user->role()->name();
        $rolesList = option('sylvainjule.bouncer.list');
        $changes   = (new Kirby\Panel\ChangesDialog())->load();

        if(array_key_exists($role, $rolesList) && array_key_exists('fieldname', $rolesList[$role])) {
            $allowed = static::getAllowedPages(kirby()->user(), $rolesList[$role]['fieldname']);

            $pages = $changes['props']['pages'];
            $pages = array_filter($pages, function($p) use($allowed) {
                return in_array($p['link'], array_column($allowed, 'path'));
            });
            $changes['props']['pages'] = array_values($pages);

            $files = $changes['props']['files'];
            $files = array_filter($files, function($f) use($allowed) {
                return in_array(explode('/files/', $f['link'])[0], array_column($allowed, 'path'));
            });
            $changes['props']['files'] = array_values($files);

            $changes['props']['users'] = [];
        }
        
        return $changes;
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
