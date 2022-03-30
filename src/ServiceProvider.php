<?php

namespace JackSleight\StatamicTailset;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $scripts = [
        __DIR__.'/../dist/js/addon.js',
    ];

    protected $stylesheets = [
        __DIR__.'/../dist/css/addon.css',
    ];

    protected $fieldtypes = [
        \JackSleight\StatamicTailset\Fieldtypes\TailsetFieldtype::class,
    ];
}