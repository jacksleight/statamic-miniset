<?php

namespace JackSleight\StatamicMiniset\Facades;

use Illuminate\Support\Facades\Facade;

class JitSafeScanner extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \JackSleight\StatamicMiniset\JitSafeScanner::class;
    }
}
