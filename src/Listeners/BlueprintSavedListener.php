<?php

namespace JackSleight\StatamicMiniset\Listeners;

use Statamic\Events\BlueprintSaved;
use JackSleight\StatamicMiniset\Facades\JitSafeManager;

class BlueprintSavedListener
{
    public function handle(BlueprintSaved $event)
    {
        if (!config('statamic.miniset.jit_safe')) {
            return;
        }
        
        JitSafeManager::processBlueprint($event->blueprint);
    }
}