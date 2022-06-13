<?php

namespace JackSleight\StatamicMiniset\Console\Commands;

use Illuminate\Console\Command;
use JackSleight\StatamicMiniset\Facades\JitSafeScanner;
use Statamic\Console\RunsInPlease;

class JitSafeScan extends Command
{
    use RunsInPlease;

    protected $name = 'statamic:miniset:jit-safe-scan';

    protected $description = 'Scan fields for classes and variants';

    public function handle()
    {
        JitSafeScanner::processAll();
    }
}
