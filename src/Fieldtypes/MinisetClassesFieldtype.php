<?php

namespace JackSleight\StatamicMiniset\Fieldtypes;

use Facades\Statamic\Fieldtypes\RowId;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class MinisetClassesFieldtype extends Fieldtype
{
    protected $categories = ['structured'];

    protected $defaultable = false;

    protected $defaultValue = [];

    public function icon()
    {
        return file_get_contents(__DIR__.'/../../resources/svg/icon.svg');
    }

    protected function configFieldItems(): array
    {
        return [
            'fields' => [
                'display' => __('Fields'),
                'type' => 'fields',
            ],
            'variants' => [
                'display' => __('Variants'),
                'type' => 'array',
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
        $fields = $this->fields()->addValues($group)->process()->values()->all();

        $group = array_merge(['id' => Arr::pull($group, '_id')], $group, $fields);

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

        $id = Arr::pull($group, 'id') ?? RowId::generate();

        return array_merge($group, $fields, [
            '_id' => $id,
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
            'tab_order' => array_keys($this->config('variants')),
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
        if (! $value) {
            return;
        }

        return implode(' ', static::generateClasses($value));
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

    public static function generateClasses(array $value)
    {
        $list = [];
        foreach ($value as $group) {
            $variant = $group['variant'] ?? null;
            $group = Arr::except($group, ['variant', 'id']);
            if ($variant && ! Str::contains($variant, '&')) {
                $variant = "{$variant}:&";
            }
            array_walk_recursive($group, function ($option) use (&$list, $variant) {
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
}
