<?php
namespace JackSleight\StatamicMiniset\Facades;

use Illuminate\Support\Facades\Facade;

class JitSafeManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \JackSleight\StatamicMiniset\JitSafeManager::class;
    }
}
