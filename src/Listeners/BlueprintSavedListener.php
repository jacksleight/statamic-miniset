<?php

namespace JackSleight\StatamicMiniset\Listeners;

use JackSleight\StatamicMiniset\Facades\JitSafeManager;
use Statamic\Events\BlueprintSaved;

class BlueprintSavedListener
{
    public function handle(BlueprintSaved $event)
    {
        if (! config('statamic.miniset.jit_safe.enable')) {
            return;
        }

        JitSafeManager::processBlueprint($event->blueprint);
    }
}
