<?php

namespace Ostheneo\Belongstomany;

use Illuminate\Validation\Rule;
use Ostheneo\Belongstomany\Rules\ArrayRules;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Fields\Searchable;
use Laravel\Nova\Fields\SupportsDependentFields;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * BelongsToMany field for Laravel Nova.
 *
 * @method static static make(string $name, string|null $attribute = null, class-string|null $resource = null)
 */
class Belongstomany extends Field
{
    use Searchable;
    use SupportsDependentFields;
    /**
     * The callback to be used for the field's options.
     */
    private $optionsCallback = null;

    public $isAction = false;
    public $height = '350px';
    public $viewable = true;
    public $showAsList = false;
    public $pivotData = [];

    /**
     * The maximum number of results to load without search.
     */
    public $optionsLimit = 1000;

    /**
     * The field's component.
     */
    public $component = 'belongstomany';

    public $relationModel = null;
    public $label = null;
    public $trackBy = 'id';
    public $resourceClass = null;
    public $resourceName = null;
    public $manyToManyRelationship = null;

    /**
     * Create a new field.
     */
    public function __construct($name, $attribute = null, $resource = null)
    {
        parent::__construct($name, $attribute);

        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);

        if ($this->label === null) {
            $this->label = $resource::$title ?? 'name';
        }

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->manyToManyRelationship = $this->attribute;

        $this->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
            if (is_subclass_of($model, 'Illuminate\Database\Eloquent\Model')) {
                $pivotData = $this->pivotData;
                $model::saved(function ($model) use ($attribute, $request, $pivotData) {
                    $inp = json_decode($request->input($attribute), true);
                    if ($inp !== null) {
                        $values = array_column($inp, 'id');
                    } else {
                        $values = [];
                    }
                    if (!empty($pivotData)) {
                        $values = array_fill_keys($values, $pivotData);
                    }
                    $model->$attribute()->sync($values);
                });
            }
        });

        $this->withMeta([
            'optionsLabel' => $this->label,
            'multiselectOptions' => [
                'selectLabel' => 'Press enter to select',
                'selectGroupLabel' => 'Press enter to select group',
                'selectedLabel' => 'Selected',
                'deselectLabel' => 'Press enter to remove',
                'deselectGroupLabel' => 'Press enter to deselect group',
            ],
            'multiselectSlots' => [
                'noOptions' => 'List is empty',
                'noResult' => 'No elements found.',
            ],
        ]);
    }

    public function optionsLabel(string $optionsLabel)
    {
        $this->label = $optionsLabel;
        return $this->withMeta(['optionsLabel' => $this->label]);
    }

    public function options(array $options)
    {
        return $this->withMeta(['options' => $options]);
    }

    public function relationModel($model)
    {
        $this->relationModel = $model;
        return $this;
    }

    public function isAction($isAction = true)
    {
        $this->isAction = $isAction;
        return $this->withMeta(['height' => $this->height]);
    }

    public function showAsListInDetail($showAsList = true)
    {
        $this->showAsList = $showAsList;
        return $this->withMeta(['showAsList' => $this->showAsList]);
    }

    public function viewable($viewable = true)
    {
        $this->viewable = $viewable;
        return $this;
    }

    public function setMultiselectProps(array $props)
    {
        return $this->withMeta(['multiselectOptions' => $props]);
    }

    public function setMultiselectSlots(array $slots)
    {
        return $this->withMeta(['multiselectSlots' => $slots]);
    }


    public function rules($rules)
    {
        $rules = ($rules instanceof Rule || is_string($rules)) ? func_get_args() : $rules;
        $this->rules = [new ArrayRules($rules)];
        return $this;
    }

    public function resolve($resource, ?string $attribute = null): void
    {
        $attribute = $attribute ?? $this->attribute;

        if (!$this->isAction && $resource && method_exists($resource, $attribute)) {
            $related = $resource->{$attribute}()->get();
            $labelField = $this->label;

            $this->value = $related->map(function ($item) use ($labelField) {
                return [
                    'id' => $item->getKey(),
                    $labelField => $item->{$labelField} ?? $item->name ?? (string) $item->getKey(),
                ];
            })->values()->toArray();
        } else {
            $this->value = [];
        }
    }

    /**
     * Set the maximum number of options to load without search.
     */
    public function optionsLimit(int $limit)
    {
        $this->optionsLimit = $limit;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return array_merge(
            parent::jsonSerialize(),
            [
                'optionsLabel' => $this->label,
                'trackBy' => $this->trackBy,
                'resourceNameRelationship' => $this->resourceName,
                'viewable' => $this->viewable,
                'value' => $this->value ?? [],
                'searchable' => $this->isSearchable(),
                'optionsLimit' => $this->optionsLimit,
            ]
        );
    }

    public function pivot()
    {
        return $this->pivotData;
    }

    public function setPivot(array $attributes)
    {
        $this->pivotData = $attributes;
        return $this;
    }

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute): void
    {
        // Do nothing - fillUsing handles this
    }
}