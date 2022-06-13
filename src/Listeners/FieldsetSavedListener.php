<?php

namespace JackSleight\StatamicMiniset\Listeners;

use JackSleight\StatamicMiniset\Facades\JitSafeScanner;
use Statamic\Events\FieldsetSaved;

class FieldsetSavedListener
{
    public function handle(FieldsetSaved $event)
    {
        if (! config('statamic.miniset.jit_safe.enable')) {
            return;
        }

        JitSafeScanner::processFieldset($event->fieldset);
    }
}
