<?php

namespace SylvainJule\Bouncer;

use Bouncer;
use Kirby\Cms\Files;
use Kirby\Cms\Pages;
use Kirby\Panel\Controller\Dialog\ChangesDialogController;
use Kirby\Panel\Controller\Search\FilesSearchController;
use Kirby\Panel\Controller\Search\PagesSearchController;

class PagesController extends PagesSearchController {
    public function models(): Pages {
        $user = $this->kirby->user();
        if(!$user) {
            return new Pages();
        }
        if(Bouncer::roleOptions($user) === null) {
            return parent::models();
        }

        $paths = array_fill_keys(Bouncer::allowedPaths($user), true);
        return $this->kirby->site()->index(true)
            ->filter(fn ($page) => isset($paths[$page->panel()->url(true)]))
            ->search($this->query)
            ->filter('isListable', true);
    }
}

class FilesController extends FilesSearchController {
    public function models(): Files {
        $user = $this->kirby->user();
        if(!$user) {
            return new Files();
        }
        if(Bouncer::roleOptions($user) === null) {
            return parent::models();
        }

        $paths = array_fill_keys(Bouncer::allowedPaths($user, true), true);
        return $this->kirby->site()->index(true)
            ->filter('isListable', true)->files()
            ->add($this->kirby->site()->files())
            ->filter(fn ($file) => isset($paths[$file->panel()->url(true)]))
            ->filter('isListable', true)->search($this->query);
    }
}

class ChangesController extends ChangesDialogController {
    public function pages(): array {
        $user = kirby()->user();
        if(!$user) {
            return [];
        }
        if(Bouncer::roleOptions($user) === null) {
            return parent::pages();
        }

        $paths = array_fill_keys(Bouncer::allowedPaths($user), true);
        return array_values(array_filter(parent::pages(), fn ($item) => isset($paths[$item['link']])));
    }

    public function files(): array {
        $user = kirby()->user();
        if(!$user) {
            return [];
        }
        if(Bouncer::roleOptions($user) === null) {
            return parent::files();
        }

        $paths = array_fill_keys(Bouncer::allowedPaths($user), true);
        return array_values(array_filter(parent::files(), fn ($item) => isset($paths[explode('/files/', $item['link'], 2)[0]])));
    }

    public function users(): array {
        $user = kirby()->user();
        return $user && Bouncer::roleOptions($user) === null ? parent::users() : [];
    }

    public function site(): ?array {
        $user = kirby()->user();

        return $user && (Bouncer::roleOptions($user) === null || in_array('/site', Bouncer::allowedPaths($user, true), true))
            ? parent::site() : null;
    }
}
