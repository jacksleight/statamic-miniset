<?php

namespace App\Tags;

use Illuminate\Support\Facades\File;
use Statamic\Fields\Value;
use Statamic\Tags\Tags;

class Lab extends Tags
{
    public function raw(): string
    {
        $value = $this->context->get($this->params->get('field'));
        $raw = $value instanceof Value ? $value->raw() : $value;

        return e(json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function jitSafe(): string
    {
        $path = config('statamic.miniset.jit_safe.file');

        return e(File::exists($path) ? File::get($path) : '');
    }
}
