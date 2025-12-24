<?php

namespace Ostheneo\Belongstomany\Http\Controllers;

use Laravel\Nova\Http\Requests\NovaRequest;

class ResourceController
{
    public function index(NovaRequest $request, $parent, $relationship, $optionsLabel, $dependsOnValue = null, $dependsOnKey = null)
    {
        $resourceClass = $request->newResource();
        $field = $resourceClass->availableFields($request)
            ->where('component', 'belongstomany')
            ->where('attribute', $relationship)
            ->first();

        if (!$field) {
            return response()->json([]);
        }

        // Get the model class and create a query builder
        $model = $field->resourceClass::newModel();
        $query = $model->newQuery();

        // Apply relatable query if the method exists
        if (method_exists($field->resourceClass, 'relatableQuery')) {
            $query = $field->resourceClass::relatableQuery($request, $query);
        }

        if ($dependsOnValue && $dependsOnKey) {
            $query = $query->where($dependsOnKey, $dependsOnValue);
        }

        return $query->get()
            ->map(function ($item) use ($optionsLabel) {
                return [
                    'id' => $item->getKey(),
                    'label' => $item->{$optionsLabel} ?? $item->name ?? (string) $item->getKey(),
                    $optionsLabel => $item->{$optionsLabel} ?? $item->name ?? (string) $item->getKey(),
                ];
            })
            ->sortBy('label')
            ->values();
    }
}
