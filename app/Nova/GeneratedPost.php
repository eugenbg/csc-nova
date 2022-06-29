<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use NumaxLab\NovaCKEditor5Classic\CKEditor5Classic;

/**
 * @property mixed $slug
 * @property mixed $source_url
 */
class GeneratedPost extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\GeneratedPost::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),
            Text::make(__('Keyword ID'), 'keyword_id')->sortable(),
            Text::make(__('Title'), 'title'),
            CKEditor5Classic::make(__('Content'), 'content')
                ->withFiles('uploads')
                ->showOnDetail(true)
                ->showOnIndex(false),
            Text::make('link', function () {
                return sprintf('<a href="%s" target="_blank">link</a>', env('APP_URL') . $this->slug);
            })->asHtml(),
            Text::make('debug', function () {
                return sprintf('<a href="%s?debug=1" target="_blank">debug</a>', env('APP_URL') . $this->slug);
            })->asHtml(),
            Text::make('source', function () {
                return $this->source_url ? sprintf('<a href="%s" target="_blank">source</a>', $this->source_url) : 'no data';
            })->asHtml()
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
