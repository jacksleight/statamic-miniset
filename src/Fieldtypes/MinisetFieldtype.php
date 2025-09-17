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

    public function augment($value)
    {
        return $this->performAugmentation($value, false);
    }

    public function shallowAugment($value)
    {
        return $this->performAugmentation($value, true);
    }

    private function performAugmentation($value, $shallow)
    {
        $method = $shallow ? 'shallowAugment' : 'augment';

        return new Values($this->fields()->addValues($value ?? [])->{$method}()->values()->all());
    }

    public function preload()
    {
        return $this->fields()->addValues($this->field->value() ?? $this->defaultGroupData())->meta()->toArray();
    }

    protected function defaultGroupData()
    {
        return $this->fields()->all()->map(function ($field) {
            return $field->fieldtype()->preProcess($field->defaultValue());
        })->all();
    }

    public function process($data)
    {
        $values = $this->fields()->addValues($data ?? [])->process()->values()->all();

        return Arr::removeNullValues($values);
    }

    public function preProcess($data)
    {
        return $this->fields()->addValues($data ?? [])->preProcess()->values()->all();
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
            ->addValues((array) $this->field->value())
            ->validator()
            ->withContext([
                'prefix' => $this->field->validationContext('prefix'),
            ])
            ->rules();

        return collect($rules)->mapWithKeys(function ($rules, $handle) {
            return [$this->field->handle().'.'.$handle => $rules];
        })->all();
    }

    public function extraValidationAttributes(): array
    {
        return collect($this->fields()->validator()->attributes())->mapWithKeys(function ($attribute, $handle) {
            return [$this->field->handle().'.'.$handle => $attribute];
        })->all();
    }

    public function preProcessValidatable($value)
    {
        return array_merge(
            $value ?? [],
            $this->fields()
                ->addValues($value ?? [])
                ->preProcessValidatables()
                ->values()
                ->all(),
        );
    }
}
