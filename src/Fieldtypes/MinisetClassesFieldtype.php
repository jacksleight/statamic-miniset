<?php

namespace JackSleight\StatamicMiniset\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Fields\Fields;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class MinisetClassesFieldtype extends Fieldtype
{
    protected $categories = ['structured'];

    protected $defaultable = false;

    protected $defaultValue = [];

    protected function configFieldItems(): array
    {
        return [
            'fields' => [
                'display' => __('Fields'),
                'instructions' => 'All fields will be combined into a single flat string of classes.',
                'type' => 'fields',
            ],
            'variants' => [
                'display' => __('Variants'),
                'instructions' => 'Options for creating groups of fields for specific variants.',
                'type' => 'array',
            ],
            'default_group_display' => [
                'display' => __('Default Group Display'),
                'instructions' => 'Label for the default (non-variant) group tab.',
                'type' => 'text',
                'default' => 'Default',
                'width' => '33',
            ],
            'jit_safe' => [
                'display' => __('JIT Safe'),
                'instructions' => 'Save a full list of possible variant classes to the blueprint/fieldset file.',
                'type' => 'toggle',
                'width' => '66',
            ],
        ];
    }

    public function process($data)
    {
        return collect($data)->map(function ($group) {
            return $this->processGroup($group);
        })->all();
    }

    private function processGroup($group)
    {
        $group = array_except($group, '_id');

        $fields = $this->fields()->addValues($group)->process()->values()->all();

        $group = array_merge($group, $fields);

        return Arr::removeNullValues($group);
    }

    public function preProcess($data)
    {
        $data = collect($data);
        
        $data = $data->pad(1, []);

        return $data->map(function ($group, $i) {
            return $this->preProcessGroup($group, $i);
        })->all();
    }

    private function preProcessGroup($group, $index)
    {
        $fields = $this->fields()->addValues($group)->preProcess()->values()->all();

        return array_merge($group, $fields, [
            '_id' => "group-$index",
        ]);
    }

    public function fields()
    {
        return new Fields($this->config('fields'), $this->field()->parent(), $this->field());
    }

    public function rules(): array
    {
        return ['array', 'min:1'];
    }

    public function extraRules(): array
    {
        return collect($this->field->value())->map(function ($group, $index) {
            return $this->groupRules($group, $index);
        })->reduce(function ($carry, $rules) {
            return $carry->merge($rules);
        }, collect())->all();
    }

    protected function groupRules($data, $index)
    {
        $rules = $this
            ->fields()
            ->addValues($data)
            ->validator()
            ->withContext([
                'prefix' => $this->field->validationContext('prefix').$this->groupRuleFieldPrefix($index).'.',
            ])
            ->rules();

        return collect($rules)->mapWithKeys(function ($rules, $handle) use ($index) {
            return [$this->groupRuleFieldPrefix($index).'.'.$handle => $rules];
        })->all();
    }

    protected function groupRuleFieldPrefix($index)
    {
        return "{$this->field->handle()}.{$index}";
    }

    public function extraValidationAttributes(): array
    {
        $attributes = $this->fields()->validator()->attributes();

        return collect($this->field->value())->map(function ($group, $index) use ($attributes) {
            return collect($group)->except('_id')->mapWithKeys(function ($value, $handle) use ($attributes, $index) {
                return [$this->groupRuleFieldPrefix($index).'.'.$handle => $attributes[$handle] ?? null];
            });
        })->reduce(function ($carry, $rules) {
            return $carry->merge($rules);
        }, collect())->filter()->all();
    }

    public function preload()
    {
        return [
            'defaults' => $this->defaultGroupData()->all(),
            'new' => $this->fields()->meta()->all(),
            'existing' => collect($this->field->value())->mapWithKeys(function ($group) {
                return [$group['_id'] => $this->fields()->addValues($group)->meta()];
            })->toArray(),
        ];
    }

    protected function defaultGroupData()
    {
        return $this->fields()->all()->map(function ($field) {
            return $field->fieldtype()->preProcess($field->defaultValue());
        });
    }

    public function augment($value)
    {
        if (!$value) {
            return;
        }

        return implode(' ', static::generateClasses($value));
    }

    public static function generateClasses(array $value)
    {
        $list = [];
        foreach ($value as $group) {
            $variant = $group['variant'] ?? null;
            unset($group['variant']);
            if ($variant && ! Str::contains($variant, '&')) {
                $variant = "{$variant}:&";
            }
            array_walk_recursive($group, function($option) use (&$list, $variant) {
                $parts = preg_split('/\s+/', $option, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($parts as $part) {
                    $list[] = $variant
                        ? str_replace('&', $part, $variant)
                        : $part;
                }
            });
        }

        return $list;
    }

    public function preProcessValidatable($value)
    {
        return collect($value)->map(function ($values) {
            $processed = $this->fields()
                ->addValues($values)
                ->preProcessValidatables()
                ->values()
                ->all();

            return array_merge($values, $processed);
        })->all();
    }
}
