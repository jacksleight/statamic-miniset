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
                'instructions' => __('statamic::fieldtypes.grid.config.fields'),
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
        return collect($data)->map(function ($variant) {
            return $this->processVariant($variant);
        })->all();
    }

    private function processVariant($variant)
    {
        $variant = array_except($variant, '_id');

        $fields = $this->fields()->addValues($variant)->process()->values()->all();

        $variant = array_merge($variant, $fields);

        return Arr::removeNullValues($variant);
    }

    public function preProcess($data)
    {
        $data = collect($data);
        
        $data = $data->pad(1, []);

        return $data->map(function ($variant, $i) {
            return $this->preProcessVariant($variant, $i);
        })->all();
    }

    private function preProcessVariant($variant, $index)
    {
        $fields = $this->fields()->addValues($variant)->preProcess()->values()->all();

        return array_merge($variant, $fields, [
            '_id' => "variant-$index",
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
        return collect($this->field->value())->map(function ($variant, $index) {
            return $this->variantRules($variant, $index);
        })->reduce(function ($carry, $rules) {
            return $carry->merge($rules);
        }, collect())->all();
    }

    protected function variantRules($data, $index)
    {
        $rules = $this
            ->fields()
            ->addValues($data)
            ->validator()
            ->withContext([
                'prefix' => $this->field->validationContext('prefix').$this->variantRuleFieldPrefix($index).'.',
            ])
            ->rules();

        return collect($rules)->mapWithKeys(function ($rules, $handle) use ($index) {
            return [$this->variantRuleFieldPrefix($index).'.'.$handle => $rules];
        })->all();
    }

    protected function variantRuleFieldPrefix($index)
    {
        return "{$this->field->handle()}.{$index}";
    }

    public function extraValidationAttributes(): array
    {
        $attributes = $this->fields()->validator()->attributes();

        return collect($this->field->value())->map(function ($variant, $index) use ($attributes) {
            return collect($variant)->except('_id')->mapWithKeys(function ($value, $handle) use ($attributes, $index) {
                return [$this->variantRuleFieldPrefix($index).'.'.$handle => $attributes[$handle] ?? null];
            });
        })->reduce(function ($carry, $rules) {
            return $carry->merge($rules);
        }, collect())->filter()->all();
    }

    public function preload()
    {
        return [
            'defaults' => $this->defaultVariantData()->all(),
            'new' => $this->fields()->meta()->all(),
            'existing' => collect($this->field->value())->mapWithKeys(function ($variant) {
                return [$variant['_id'] => $this->fields()->addValues($variant)->meta()];
            })->toArray(),
        ];
    }

    protected function defaultVariantData()
    {
        return $this->fields()->all()->map(function ($field) {
            return $field->fieldtype()->preProcess($field->defaultValue());
        });
    }

    public function augment($value)
    {
        $list = [];
        foreach ($value as $group) {
            $variants = $group['variants'] ?? null;
            unset($group['variants']);
            $prefix = $variants
                ? implode(':', $variants) . ':'
                : '';
            array_walk_recursive($group, function($v) use (&$list, $prefix) {
                $list[] = $prefix . $v;
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
