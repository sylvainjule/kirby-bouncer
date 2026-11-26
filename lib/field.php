<?php

namespace SylvainJule\Bouncer;

use Bouncer;
use Kirby\Form\Field\DisplayField;

class BouncerNavField extends DisplayField {
    public function props(): array {
        return [
            ...parent::props(),
            'bouncerUser' => Bouncer::currentUser(kirby()->user()),
            'bouncerParent' => $this->model()?->panel()->url(true)
        ];
    }
}
