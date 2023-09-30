<?php

namespace Oxygencms\OxyNova\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use MrMonat\Translatable\Translatable;
use Oxygencms\OxyNova\Traits\SortTranslatableFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class Phrase extends Resource
{
    use SortTranslatableFields;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = OXYGEN_PHRASE;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'message';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['key', 'message'];

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string
     */
    public function subtitle()
    {
        return "Key: {$this->key}";
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $groups = $this::getGroups();

        return [
            Select::make('group')
                  ->options($groups)
                  ->sortable()
                  ->rules('required', 'in:' . implode(',', $groups)),

            Text::make('key')
                ->sortable()
                ->rules(
                    'required',
                    'string',
                    'max:140',
                    "unique:phrases,key,{{resourceId}},id,group,$request->group"
                ),

            Translatable::make('message')
                        ->singleLine()
                        ->rules('required', 'array', 'distinct')
                        ->sortable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
