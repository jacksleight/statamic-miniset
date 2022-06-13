<?php

namespace JackSleight\StatamicMiniset\Console\Commands;

use Illuminate\Console\Command;
use JackSleight\StatamicMiniset\Facades\JitSafeManager;
use Statamic\Console\RunsInPlease;

class JitSafeScan extends Command
{
    use RunsInPlease;

    protected $name = 'miniset:jit-safe-scan';

    public function handle()
    {
        JitSafeManager::processAll();
    }
}
