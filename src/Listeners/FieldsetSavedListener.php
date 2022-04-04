<?php

namespace JackSleight\StatamicMiniset\Listeners;

use JackSleight\StatamicMiniset\Facades\JitSafeManager;
use Statamic\Events\FieldsetSaved;

class FieldsetSavedListener
{
    public function handle(FieldsetSaved $event)
    {
        if (! config('statamic.miniset.classes.jit_safe.enable')) {
            return;
        }

        JitSafeManager::processFieldset($event->fieldset);
    }
}
