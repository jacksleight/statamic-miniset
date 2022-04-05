<?php

namespace JackSleight\StatamicMiniset;

use JackSleight\StatamicMiniset\Fieldtypes\MinisetClassesFieldtype;
use JackSleight\StatamicMiniset\Fieldtypes\MinisetFieldtype;
use JackSleight\StatamicMiniset\Listeners\BlueprintSavedListener;
use JackSleight\StatamicMiniset\Listeners\FieldsetSavedListener;
use Statamic\Events\BlueprintSaved;
use Statamic\Events\FieldsetSaved;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $scripts = [
        __DIR__.'/../dist/js/addon.js',
    ];

    protected $stylesheets = [
        __DIR__.'/../dist/css/addon.css',
    ];

    protected $listen = [
        BlueprintSaved::class => [BlueprintSavedListener::class],
        FieldsetSaved::class  => [FieldsetSavedListener::class],
    ];

    protected $fieldtypes = [
        MinisetClassesFieldtype::class,
        MinisetFieldtype::class,
    ];

    public function register()
    {
        parent::register();

        $this->mergeConfigFrom(
            __DIR__.'/../config/statamic/miniset.php', 'statamic.miniset',
        );
    }

    public function bootAddon()
    {
        $this->publishes([
            __DIR__.'/../config/statamic/miniset.php' => config_path('statamic/miniset.php'),
        ], 'statamic-miniset-config');
    }
}
