<?php

use Illuminate\Support\Facades\File;
use JackSleight\StatamicMiniset\Facades\JitSafeScanner;
use Statamic\Facades\Blueprint;
use Statamic\Facades\YAML;

beforeEach(function () {
    $this->file = sys_get_temp_dir().'/miniset-tests/'.uniqid().'/miniset_classes.yaml';
    config(['statamic.miniset.jit_safe.file' => $this->file]);
});

afterEach(function () {
    File::deleteDirectory(dirname($this->file, 2));
});

function scannedClasses($file)
{
    return File::exists($file) ? YAML::file($file)->parse() : [];
}

function classesBlueprint(array $options)
{
    return Blueprint::make('page')->setNamespace('collections.pages')->setContents([
        'fields' => [
            [
                'handle' => 'classes',
                'field' => [
                    'type' => 'miniset_classes',
                    'variants' => ['lg' => 'Desktop'],
                    'fields' => [
                        ['handle' => 'size', 'field' => ['type' => 'select', 'options' => $options]],
                    ],
                ],
            ],
        ],
    ]);
}

it('scans map options', function () {
    JitSafeScanner::processBlueprint(classesBlueprint([
        'text-sm' => 'Small',
        'text-lg' => 'Large',
    ]));

    expect(scannedClasses($this->file))->toBe([
        'blueprints.collections.pages.page' => ['text-sm', 'text-lg', 'lg:text-sm', 'lg:text-lg'],
    ]);
});
