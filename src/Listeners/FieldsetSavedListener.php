<?php

namespace JackSleight\StatamicMiniset\Listeners;

use Statamic\Events\FieldsetSaved;
use JackSleight\StatamicMiniset\Facades\JitSafeManager;

class FieldsetSavedListener
{
    public function handle(FieldsetSaved $event)
    {
        if (!config('statamic.miniset.jit_safe')) {
            return;
        }
        
        JitSafeManager::processFieldset($event->fieldset);
    }
}