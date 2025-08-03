# Kirby – Bouncer

Restrict access of a user role to a specific page (and its children) in the panel.

![bouncer-screenshot](https://user-images.githubusercontent.com/14079751/76368370-4c6ccc00-6330-11ea-92d3-9ac560cf037e.jpg)

<br/>

## Overview

> This plugin is completely free and published under the MIT license. However, if you are using it in a commercial project and want to help me keep up with maintenance, you can consider [making a donation of your choice](https://www.paypal.me/sylvainjl).

- [1. Installation](#1-installation)
- [2. Setup](#2-setup)
- [3. Disclaimer](#3-disclaimer)
- [4. License](#4-disclaimer)

<br/>

## 1. Installation

Download and copy this repository to ```/site/plugins/bouncer```

Alternatively, you can install it with composer: ```composer require sylvainjule/bouncer```

<br/>

## 2. Setup

The intent of this plugin is to limit a user's ability to edit only their Account page + a page (and its children) selected in a *Pages field*.

- First, create a new user role (for example, `/site/blueprints/users/test.yml`)
- Set their permissions, and add a `pages` field:

```yaml
title: Test

permissions:
  access:
    panel: true
    site: true
    settings: false
    languages: false
    users: false
  # ...
  user:
    changeRole: false
    delete: false
    update: false # else a user will be able to edit the page they have access to on their profile

fields:
  canaccess:
    label: 'The user will only be able to access:'
    type: pages
    multiple: false
    options: query
    query: site.pages # or any query that suits your needs
```

- In your `site/config/config.php`, tell the plugin which `role => fieldname` associations to use:

```php
return [
    'sylvainjule.bouncer.list' => [
        'test' => [ // match the filename without extension of the user blueprint
            'fieldname' => 'canaccess' 
        ]
    ]
];

```

### 2.1 Allow additionnal paths

In your `site/config/config.php`, you can configure for each `role` some extra paths the user will be able to visit. 
It can be useful if you have custom panel areas, for example.

```php
return [
    'sylvainjule.bouncer.list' => [
        'test' => [ // match the filename without extension of the user blueprint
            'extra' => [
                [
                    'title' => 'Area title',
                    'path'  => '/area-path'
                ]
            ]
        ]
    ]
];
```

### 2.2 Optional page switcher

(beta)

Since 1.0.1 a given user can access different pages. You can remove the `multiple: false` option from the blueprint:

```yaml
# User role blueprint
title: Test

fields:
  canaccess:
    label: 'The user will only be able to access:'
    type: pages
    options: query
    query: site.pages # or any query that suits your needs
```

Add a `bouncernav` section in every page you'd like to display the page switcher on:

```php
// Anywhere in any blueprint

(...)
sections:
  bouncernav:
    type: bouncernav
```

Then state in your `config.php` that you want to display the page switcher for a given user role:

```php
return [
    'sylvainjule.bouncer.list' => [
        'test' => [
            'fieldname' => 'canaccess',
            'nav' => true
        ]
    ]
];
```

### 2.3 Fallback path

In your `site/config/config.php`, you can configure for each `role` which path to fallback to when the user tries to access a forbidden page. 
It is optional: if left empty, the first accessible page from the *Pages field* associated to the user's role (`canaccess`, in our example) will be used.

```php
return [
    'sylvainjule.bouncer.list' => [
        'test' => [ // match the filename without extension of the user blueprint
            'fallback' => '/fallback-path'
        ]
    ]
];
```

### 2.4 Movable pages

By default, a restricted user will be able to move pages even to pages it cannot access. 
Kirby calls a `isMovableTo` method to check which pages to disable / enable in the *Move page* dialog, which is declared in the `Page` class and cannot be overwritten globally from the plugin.
However if you want to apply the bouncer's restrictions to this page tree and disable restricted pages, the plugin provides a replacement method.

You have to declare a custom [Page model](https://getkirby.com/docs/guide/templates/page-models#overriding-the-page-class) for each page in order to apply it:

```php
class ExamplePage extends Page {
    public function isMovableTo(Kirby\Cms\Page|Kirby\Cms\Site $parent): bool {
        return Bouncer::isMovableTo($this, $parent);
    }
};
```


<br/>

## 3. Disclaimer

I needed this functionnality for a website and turned it into a plugin. I hope it can prove helpful, but do not intend to extend it or support more refined restriction scenarios with this plugin.

<br/>

## 4. License

MIT
