<?php

namespace JackSleight\StatamicMiniset;

use JackSleight\StatamicMiniset\Fieldtypes\MinisetClassesFieldtype;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Fieldset;

class JitSafeManager
{
    public function processFieldset(Fieldset $fieldset)
    {
        $key = 'fieldsets.'.$fieldset->handle();

        $this->processContents($key, $fieldset->contents());
    }

    public function processBlueprint(Blueprint $blueprint)
    {
        $key = 'blueprints.'.$blueprint->namespace().'.'.$blueprint->handle();

        $this->processContents($key, $blueprint->contents());
    }

    protected function processContents($key, $contents)
    {
        $configs = $this->findConfigs($contents);
        $classes = $this->processConfigs($configs);

        $path = config('statamic.miniset.jit_safe.file');
        $dir = dirname($path);

        if (! File::exists($dir)) {
            File::makeDirectory($dir);
        }
        $data = File::exists($path)
            ? YAML::file($path)->parse()
            : [];
        if (count($classes)) {
            $data[$key] = $classes;
        } else {
            unset($data[$key]);
        }
        File::put($path, YAML::dump($data));
    }

    protected function findConfigs($contents)
    {
        $configs = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveArrayIterator($contents),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $value) {
            if (! is_array($value)) {
                continue;
            }
            if (($value['type'] ?? null) === 'miniset_classes' && is_array($value['fields'] ?? null)) {
                $configs[] = $value;
            }
        }

        return $configs;
    }

    protected function processConfigs($configs)
    {
        $classes = [];

        foreach ($configs as $config) {
            $variants = array_merge(
                [null],
                array_keys($config['variants'] ?? [])
            );

            $options = call_user_func_array('array_merge', array_map(function ($field) {
                return array_keys($field['field']['options'] ?? []);
            }, $config['fields']));

            $value = array_map(function ($variant) use ($options) {
                return [
                    'variant' => $variant,
                    'options' => $options,
                ];
            }, $variants);

            $classes = array_merge($classes, MinisetClassesFieldtype::generateClasses($value));
        }

        return array_values(array_unique($classes));
    }
}
