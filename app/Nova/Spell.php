<?php

namespace App\Nova;

use App\Nova\Actions\TestSpell;
use App\Services\TextGenerationService;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use NumaxLab\NovaCKEditor5Classic\CKEditor5Classic;

class Spell extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Spell::class;

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
            Text::make(__('Title'), 'title'),
            Textarea::make(__('Prompt'), 'prompt')
                ->showOnDetail(true)
                ->showOnIndex(false),
            Text::make(__('Tokens'), 'tokens')
                ->showOnIndex(false),
            Text::make(__('temperature'), 'temperature')
                ->showOnIndex(false),
            Text::make(__('top_p'), 'top_p')
                ->showOnIndex(false),
            Text::make(__('frequency_penalty'), 'frequency_penalty')
                ->showOnIndex(false),
            Text::make(__('presence_penalty'), 'presence_penalty')
                ->showOnIndex(false),
            Text::make(__('STOP SEQUENCES JSON'), 'stop_sequences')
                ->showOnIndex(false),
            Select::make(__('engine'), 'engine')
                ->options([
                    'ada' => 'ada',
                    'babbage' => 'babbage',
                    'curie' => 'curie',
                    'curie-instruct-beta' => 'curie-instruct-beta',
                    'davinci' => 'davinci',
                    'davinci-instruct-beta' => 'davinci-instruct-beta',
                    'davinci:ft-personal-2022-07-14-10-12-57' => 'Davinci Trained Rewrite',
                    'curie:ft-personal-2022-07-14-10-14-25' => 'Curie Trained Rewrite',
                ])
                ->showOnIndex(true),
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
        return [
            new TestSpell
        ];
    }
}
