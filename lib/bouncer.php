<?php

use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use SylvainJule\Bouncer\ChangesController;
use SylvainJule\Bouncer\FilesController;
use SylvainJule\Bouncer\PagesController;

class Bouncer {
    public static function roleOptions($user): ?array {
        if(!$user) { return null; }

        $list = kirby()->option('sylvainjule.bouncer.list', []);
        if(!is_array($list)) { return null; }

        $role = $user->role()->name();
        return array_key_exists($role, $list) ? (array)$list[$role] : null;
    }

    public static function getAllowedPages($user, $fieldname, $extra = false) {
        $allowed = [];
        $pages = new Pages();

        if($user && is_string($fieldname) && $fieldname !== '') {
            $field = $user->content()->get($fieldname);

            try {
                $ids = $field->yaml();
            } 
            catch (\Exception $e) {
                $ids = [];
            }
            foreach($ids as $id) {
                if(is_string($id) && $id !== '' && ($page = kirby()->page($id)) instanceof Page) {
                    $pages->add($page);
                }
            }
        }

        foreach($pages as $page) {
            $allowed[] = [
                'title' => $page->title()->value(),
                'path'  => $page->panel()->url(true)
            ];
            if($extra) {
                $allowed = array_merge($allowed, static::getChildren($page));
            }
        }

        if($extra) {
            $allowed = array_merge($allowed, [
                ['title' => 'Account', 'path' => '/account'],
                ['title' => 'Login', 'path' => '/login'],
                ['title' => 'Logout', 'path' => '/logout'],
                ['title' => 'Reset password', 'path' => '/reset-password']
            ], static::roleOptions($user)['extra'] ?? []);
        }
        return $allowed;
    }

    public static function currentUser($user): array {
        $options = static::roleOptions($user);
        return [
            'nav'     => ($options['nav'] ?? false) ?: false,
            'allowed' => $options === null ? [] : static::getAllowedPages($user, $options['fieldname'] ?? null)
        ];
    }

    public static function allowedPaths($user, bool $extra = false): array {
        $options = static::roleOptions($user);
        if($options === null) { return []; }

        return array_column(static::getAllowedPages($user, $options['fieldname'] ?? null, $extra), 'path');
    }

    public static function isAllowedView(string $path, array $paths): bool {
        if(in_array($path, $paths, true)) {
            return true;
        }
        // ↓ live preview
        if(preg_match('~^((?:/pages/[^/]+|/site))/preview/(?:changes|latest|compare|form(?:/remote)?)$~D', $path, $match)) {
            return in_array($match[1], $paths, true);
        }
        return $path === '/site/tree' || str_starts_with($path, '/site/tree/');
    }

    public static function redirectPath($user, string $path): ?string {
        $options = static::roleOptions($user);

        if($options === null) {
            return null;
        }

        $path  = '/' . trim($path, '/');
        $paths = static::allowedPaths($user, true);
        if(static::isAllowedView($path, $paths)) {
            return null;
        }

        $fallback = $options['fallback'] ?? null;
        if(is_string($fallback) && $fallback !== '') {
            $fallback = '/' . ltrim($fallback, '/');
            $target   = parse_url($fallback, PHP_URL_PATH);

            if(is_string($target) && static::isAllowedView($target, $paths)) {
                return $fallback;
            }
        }

        return $paths[0] ?? '/account';
    }

    public static function isMovableTo($page, $parent) {
        try {
            $user = kirby()->user();
            if(!$user) { return false; }

            $options = static::roleOptions($user);
            if($options !== null && array_key_exists('fieldname', $options) &&
                !in_array($parent->panel()->url(true), static::allowedPaths($user), true)) {
                return false;
            }

            $page->guards()->ensureExecutable('move', $parent);

            return true;
        } 
        catch (Throwable) {
            return false;
        }
    }

    public static function panelSearch($kirby, $query, $limit, $page, $type = 'pages') {
        if(!$kirby->user()) {
            return ['results' => [], 'pagination' => null];
        }

        $class = $type === 'files' ? FilesController::class : PagesController::class;
        return (new $class(query: $query ?? '', limit: $limit, page: $page))->load();
    }

    public static function panelChanges() {
        return (new ChangesController())->load()->render();
    }

    private static function getChildrenFiles(Page $page) {
        $allowed = [];
        foreach($page->files() as $file) {
            $allowed[] = [
                'title' => $file->title()->value(),
                'path' => $file->panel()->url(true)
            ];
        }
        return $allowed;
    }

    private static function getChildren(Page $page) {
        $allowed = static::getChildrenFiles($page);

        foreach($page->childrenAndDrafts() as $child) {
            $allowed[] = [
                'title' => $child->title()->value(),
                'path' => $child->panel()->url(true)
            ];
            $allowed = array_merge($allowed, static::getChildren($child));
        }
        
        return $allowed;
    }
}
