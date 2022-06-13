<?php

namespace JackSleight\StatamicMiniset;

use Facades\Statamic\Fields\FieldRepository as FieldFacade;
use JackSleight\StatamicMiniset\Fieldtypes\MinisetClassesFieldtype;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Facades\Fieldset as FieldsetFacade;
use Statamic\Facades\File;
use Statamic\Facades\Taxonomy as TaxonomyFacade;
use Statamic\Facades\YAML;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Fieldset;

class JitSafeScanner
{
    public function processAll()
    {
        foreach (CollectionFacade::all() as $collection) {
            foreach ($collection->entryBlueprints() as $blueprint) {
                $this->processBlueprint($blueprint);
            }
        }
        foreach (TaxonomyFacade::all() as $taxonomy) {
            foreach ($taxonomy->termBlueprints() as $blueprint) {
                $this->processBlueprint($blueprint);
            }
        }
        foreach (FieldsetFacade::all() as $fieldset) {
            $this->processFieldset($fieldset);
        }
    }

    public function processFieldset(Fieldset $fieldset)
    {
        $key = 'fieldsets.'.$fieldset->handle();

        $this->processData($key, $fieldset->contents());
    }

    public function processBlueprint(Blueprint $blueprint)
    {
        $key = 'blueprints.'.$blueprint->namespace().'.'.$blueprint->handle();

        $this->processData($key, $blueprint->contents());
    }

    protected function processData($key, $data)
    {
        $configs = $this->findConfigs($data);
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

    protected function findConfigs($data)
    {
        $configs = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveArrayIterator($data),
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

            $stack = $config['fields'];
            $options = [];
            while (count($stack)) {
                $field = array_shift($stack);
                if (is_string($field['field'] ?? null)) {
                    if ($imports = FieldFacade::find($field['field'])) {
                        array_push($stack, ['field' => array_merge($imports->toArray(), $field['config'] ?? [])]);
                    }
                    continue;
                } elseif (is_string($field['import'] ?? null)) {
                    if ($imports = FieldsetFacade::find($field['import'])) {
                        call_user_func_array('array_push', array_merge([&$stack], $imports->contents()['fields']));
                    }
                    continue;
                }
                $options = array_merge($options, array_keys($field['field']['options'] ?? []));
            }

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
