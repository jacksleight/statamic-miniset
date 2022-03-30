<?php

namespace JackSleight\StatamicTailset\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Fields\Fields;
use Statamic\Facades\GraphQL;
use Statamic\Support\Arr;

class TailsetFieldtype extends Fieldtype
{
    protected $categories = ['structured'];
    protected $defaultable = false;
    protected $defaultValue = [];

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
            'variantSeparator' => [
                'display' => __('Variant Separator'),
                'type' => 'text',
                'width' => '25',
                'default' => ':',
            ],
            'variantPosition' => [
                'display' => __('Variant Position'),
                'type' => 'button_group',
                'width' => '25',
                'default' => 'before',
                'options' => [
                    'before' => 'Before',
                    'after'  => 'After',
                ],
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
        
        $data = $data->pad(1, [
            'variants' => [],
        ]);

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

        $separator = $this->config('variantSeparator');
        $position  = $this->config('variantPosition');
        
        $list = [];
        foreach ($value as $group) {
            $variants = $group['variants'] ?? [];
            unset($group['variants']);
            $affix = implode(array_map(function($v) use ($position, $separator) {
                return $position === 'before'
                    ? $v . $separator
                    : $separator . $v;
            }, $variants));
            array_walk_recursive($group, function($v) use (&$list, $affix, $position) {
                $list[] = $position === 'before'
                    ? $affix . $v
                    : $v . $affix;
            });
        }

        $class = implode(' ', $list);

        return $class;
    }

    public function toGqlType()
    {
        return GraphQL::listOf(GraphQL::type($this->gqlItemTypeName()));
    }

    // public function addGqlTypes()
    // {
    //     GraphQL::addType($type = new GridItemType($this, $this->gqlItemTypeName()));

    //     $this->fields()->all()->each(function ($field) {
    //         $field->fieldtype()->addGqlTypes();
    //     });
    // }

    // private function gqlItemTypeName()
    // {
    //     return 'GridItem_'.collect($this->field->handlePath())->map(function ($part) {
    //         return Str::studly($part);
    //     })->join('_');
    // }

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
