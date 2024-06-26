<?php

namespace Oxygencms\OxyNova\Nova;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Panel;
use Spatie\NovaTranslatable\Translatable;
use Oxygencms\OxyNova\MediaCollections;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\Trix;

class PageSection extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = OXYGEN_PAGE_SECTION;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
        'body',
    ];

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array
     */
    public static $with = ['page'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $name_format = '/^[0-9a-z-_]+$/u';

        return [
            ID::make('id')->sortable()->onlyOnDetail(),

            Boolean::make('active')
                   ->sortable()
                   ->rules('required', 'boolean'),

            Text::make('name')
                ->sortable()
                ->rules(
                    'required',
                    'string',
                    "regex:$name_format",
                    "unique:page_sections,name,{{resourceId}},id,page_id,$request->page"
                )
                ->onUpdateReadOnly(),

            Translatable::make([
                  Trix::make('Body')
                       ->hideFromIndex()
                       ->hideFromDetail()
                       ->rules('sometimes', 'distinct', function ($attribute, $value, $fail) {
                            $validator = Validator::make(
                                [$attribute => $value],
                                ["{$attribute}.*" => 'nullable|string']
                            );

                            if ($validator->fails())
                                $fail($validator->errors()->first());
                        }),
                    Text::make('Body')->onlyOnDetail()->asHtml(),
            ]),

            BelongsTo::make('Page', 'page', Page::class),

            new Panel('Media', function () use ($request) {
                return [
                    MediaCollections::imagesField($request),
                ];
            }),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request $request
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
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
