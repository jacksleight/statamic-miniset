<?php

namespace JackSleight\StatamicMiniset\Fieldtypes;

use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Values;
use Statamic\Support\Arr;

class MinisetFieldtype extends Fieldtype
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
                'full_width_setting' => true,
            ],
        ];
    }

    public function augment($values)
    {
        return $this->performAugmentation($values, false);
    }

    public function shallowAugment($values)
    {
        return $this->performAugmentation($values, true);
    }

    protected function performAugmentation($values, $shallow)
    {
        $augmentMethod = $shallow ? 'shallowAugment' : 'augment';

        $values = $this->fields()->addValues($values)->{$augmentMethod}()->values();

        return new Values($values->all());
    }

    public function process($data)
    {
        $fields = $this->fields()->addValues($data)->process()->values()->all();

        return Arr::removeNullValues($fields);
    }

    public function preProcess($data)
    {
        $fields = $this->fields()->addValues($data)->preProcess()->values()->all();

        return $fields;
    }

    public function fields()
    {
        return new Fields($this->config('fields'), $this->field()->parent(), $this->field());
    }

    public function rules(): array
    {
        return ['array'];
    }

    public function extraRules(): array
    {
        $rules = $this
            ->fields()
            ->addValues($this->field->value() ?? [])
            ->validator()
            ->rules();

        return collect($rules)->mapWithKeys(function ($rules, $handle) {
            return [$this->field->handle().'.'.$handle => $rules];
        })->all();
    }

    public function extraValidationAttributes(): array
    {
        $attributes = $this
            ->fields()
            ->validator()
            ->attributes();

        return collect($this->field->value())->mapWithKeys(function ($value, $handle) use ($attributes) {
            return [$this->field->handle().'.'.$handle => $attributes[$handle] ?? null];
        })->filter()->all();
    }

    public function preload()
    {
        return [
            'defaults' => $this->fields()->all()->map(function ($field) {
                return $field->fieldtype()->preProcess($field->defaultValue());
            })->all(),
        ];
    }

    public function preProcessValidatable($value)
    {
        $processed = $this->fields()
            ->addValues($value)
            ->preProcessValidatables()
            ->values()
            ->all();

        return $processed;
    }
}
