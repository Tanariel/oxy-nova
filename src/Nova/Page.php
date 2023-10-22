<?php

namespace Oxygencms\OxyNova\Nova;

use Laravel\Nova\Panel;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Spatie\NovaTranslatable\Translatable;
use Oxygencms\OxyNova\MediaCollections;
use Illuminate\Support\Facades\Validator;
use Oxygencms\OxyNova\Traits\SortTranslatableFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\Trix;

class Page extends Resource
{
    use SortTranslatableFields;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = OXYGEN_PAGE;

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
    public static $search = ['name', 'title', 'slug'];

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string
     */
    public function subtitle()
    {
        return "Slug: {$this->slug}";
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function fields(NovaRequest $request)
    {
        $layouts     = $this::getLayouts();
        $templates   = $this::getTemplates();
        $name_format = '/^[a-z_-]+$/u';

        return array_filter([
            ID::make('id')->onlyOnDetail(),

            Boolean::make('Active')
                   ->sortable(),

            Text::make('System name', 'name')
                ->rules('required', 'string', "regex:$name_format", 'max:140')
                ->creationRules('unique:pages')
                ->updateRules("unique:pages,name,{{resourceId}}")
                ->sortable(),

            Select::make('Layout')
                  ->options($layouts)
                  ->rules('required', 'string', 'in:' . implode(',', $layouts))
                  ->hideFromIndex(),

            Select::make('Template')
                  ->options($templates)
                  ->rules('required', 'string', 'in:' . implode(',', $templates))
                  ->hideFromIndex(),

            new Panel('SEO', $this->getSeoPanelFields($request)),

            new Panel('Content', $this->getContentPanelFields()),

            new Panel('Media', function () use ($request) {
                return [
                    MediaCollections::imagesField($request),
                ];
            }),

            $this->getHasManySectionsField(),
        ]);
    }

    /**
     * Define the SEO panel's fields.
     *
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    protected function getSeoPanelFields(NovaRequest $request)
    {
        return [
            Translatable::make(
                [
                    Text::make('Slug')
                        ->rules('required', 'distinct', function ($attribute, $value, $fail) use ($request) {
                            $slug_format = '/^[а-я0-9a-z-\/]+$/u';

                            $slug_rules = ['required', 'string', 'max:140', "regex:$slug_format"];

                            $request->isMethod('post')
                                ? array_push($slug_rules, "unique_translation:pages")
                                : array_push($slug_rules, "unique_translation:pages,slug,$request->resourceId");

                            $validator = Validator::make([$attribute => $value], ["{$attribute}.*" => $slug_rules]);

                            if ($validator->fails())
                                $fail($validator->errors()->first());
                        }),
                    Text::make('Title')->hideFromIndex(),
                    Text::make('Meta description')->hideFromIndex(),
                    Text::make('Meta keywords')->hideFromIndex(),
                ])
        ];
    }

    /**
     * Define the content panel's fields.
     *
     * @return array
     */
    protected function getContentPanelFields()
    {
        return [
            Translatable::make(
                [
                    Text::make('Summary')->hideFromIndex(),
                    Trix::make('Body')
                        ->hideFromIndex()
                        ->hideFromDetail(),
                    Text::make('Body')->onlyOnDetail()->asHtml()
                
                ])
        ];
    }

    /**
     * @return HasMany
     */
    protected function getHasManySectionsField()
    {
        if ( ! $this->isSoftDeleted()) {
            return HasMany::make('Page Sections', 'sections', PageSection::class);
        }
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
