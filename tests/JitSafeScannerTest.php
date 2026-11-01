<?php

use Illuminate\Support\Facades\File;
use JackSleight\StatamicMiniset\Facades\JitSafeScanner;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\YAML;

beforeEach(function () {
    $this->file = sys_get_temp_dir().'/miniset-tests/'.uniqid().'/miniset_classes.yaml';
    config(['statamic.miniset.jit_safe.file' => $this->file]);
    Blueprint::setDirectory(dirname($this->file).'/blueprints');
});

afterEach(function () {
    File::deleteDirectory(dirname($this->file, 2));
});

function scannedClasses($file)
{
    return File::exists($file) ? YAML::file($file)->parse() : [];
}

function classesBlueprint(array $options, $handle = 'page', $namespace = 'collections.pages')
{
    return Blueprint::make($handle)->setNamespace($namespace)->setContents([
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

it('scans key value list options', function () {
    JitSafeScanner::processBlueprint(classesBlueprint([
        ['key' => 'font-normal', 'value' => 'Normal'],
        ['key' => 'font-bold', 'value' => 'Bold'],
    ]));

    expect(scannedClasses($this->file))->toBe([
        'blueprints.collections.pages.page' => ['font-normal', 'font-bold', 'lg:font-normal', 'lg:font-bold'],
    ]);
});

it('scans plain list options', function () {
    JitSafeScanner::processBlueprint(classesBlueprint(['italic', 'underline']));

    expect(scannedClasses($this->file))->toBe([
        'blueprints.collections.pages.page' => ['italic', 'underline', 'lg:italic', 'lg:underline'],
    ]);
});

it('does nothing from the scan command when disabled', function () {
    config(['statamic.miniset.jit_safe.enable' => false]);
    Collection::make('pages')->save();
    classesBlueprint(['p-4' => 'Small'])->save();

    $this->artisan('statamic:miniset:jit-safe-scan')->assertSuccessful();

    expect(File::exists($this->file))->toBeFalse();
});

it('removes stale keys on a full scan', function () {
    config(['statamic.miniset.jit_safe.enable' => true]);
    Collection::make('pages')->save();
    classesBlueprint(['p-4' => 'Small'])->save();
    File::put($this->file, YAML::dump(['blueprints.collections.ghost.gone' => ['stale-class']]));

    $this->artisan('statamic:miniset:jit-safe-scan')->assertSuccessful();

    expect(scannedClasses($this->file))->toBe([
        'blueprints.collections.pages.page' => ['p-4', 'lg:p-4'],
    ]);
});

it('covers every blueprint the save listeners scan on a full scan', function () {
    config(['statamic.miniset.jit_safe.enable' => true]);
    classesBlueprint(['p-1' => 'One'], 'settings', 'globals')->save();
    classesBlueprint(['p-2' => 'Two'], 'main', 'navigation')->save();
    classesBlueprint(['p-3' => 'Three'], 'images', 'assets')->save();
    classesBlueprint(['p-4' => 'Four'], 'contact', 'forms')->save();
    classesBlueprint(['p-5' => 'Five'], 'user', null)->save();
    classesBlueprint(['p-6' => 'Six'], 'user_group', null)->save();
    $listened = scannedClasses($this->file);
    File::delete($this->file);

    $this->artisan('statamic:miniset:jit-safe-scan')->assertSuccessful();

    expect($listened)->toHaveCount(6)
        ->and(scannedClasses($this->file))->toEqualCanonicalizing($listened);
});
