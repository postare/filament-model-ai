<?php

namespace Postare\ModelAi\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;

class ModelAiPage extends \Filament\Pages\Page
{
    use InteractsWithForms;

    public ?array $data = [];

    public static function getSlug(): string
    {
        return config('model-ai.slug');
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return __('filament-model-ai::model-ai.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-model-ai::model-ai.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return config('model-ai.use_navigation_group') ? __('filament-model-ai::model-ai.navigation_group') : null;
    }

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static string $view = 'filament-model-ai::filament.pages.model-ai';

    private array $selected_columns;

    public $ai_response;

    public bool $finished = false;

    public bool $question = false;

    public function mount(): void
    {
        $this->form->fill([
            'ai_model' => config('model-ai.default_openai_model'),
        ]);
    }

    public function form(Form $form): Form
    {

        $default_openai_model = config('model-ai.default_openai_model');
        $eloquent_model = config('model-ai.eloquent_model');
        $field_label = config('model-ai.field_label');
        $field_id = config('model-ai.field_id');
        $predefined_prompts_actions = $this->predefinedPromptsToActions(config('model-ai.predefined_prompts', []));
        $selected_columns = config('model-ai.selected_columns');

        $disabled_openai_model_selection = config('model-ai.disable_openai_model_selection');

        return $form
            ->schema([

                Forms\Components\Select::make('item')
                    ->label(__('filament-model-ai::model-ai.form.item'))
                    ->live(false, 500)
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => $eloquent_model::where($field_label, 'like', "%{$search}%")->limit(50)->pluck($field_label, $field_id)->toArray())
                    ->afterStateUpdated(function (Forms\Set $set, $state) use ($eloquent_model, $selected_columns, $field_id) {
                        $data = $eloquent_model::select($selected_columns)->where($field_id, $state)->first();
                        $set('context_data', $data->toJson(JSON_PRETTY_PRINT));
                    })
                    ->required(),

                Forms\Components\Select::make('ai_model')
                    ->label(__('filament-model-ai::model-ai.form.ai_model'))
                    ->options(function () use ($default_openai_model, $disabled_openai_model_selection) {
                        if ($disabled_openai_model_selection) {
                            return [$default_openai_model => $default_openai_model];
                        }

                        return \Postare\ModelAi\ModelAi::chat()->listModels();
                    })
                    ->disabled($disabled_openai_model_selection)
                    ->required(),

                Forms\Components\Textarea::make('context_data')
                    ->label(__('filament-model-ai::model-ai.form.context_data'))
                    ->hidden(fn (Forms\Get $get) => ! $get('item'))
                    ->rows(5)
                    ->columnSpanFull()
                    ->required(),

                Forms\Components\Textarea::make('prompt')
                    ->label(__('filament-model-ai::model-ai.form.prompt'))
                    ->rows(3)
                    ->placeholder(__('filament-model-ai::model-ai.form.prompt_placeholder'))
                    ->live()
                    ->required()
                    ->helperText(__('filament-model-ai::model-ai.form.prompt_helper_text'))
                    ->columnSpanFull(),

                Forms\Components\Fieldset::make(__('filament-model-ai::model-ai.form.predefined_prompts_fieldset'))
                    ->hidden(count($predefined_prompts_actions) == 0)
                    ->schema([
                        Forms\Components\Actions::make($predefined_prompts_actions)->columnSpanFull(),
                    ]),

            ])
            ->columns(2)
            ->statePath('data');
    }

    protected function predefinedPromptsToActions($prompts = []): array
    {
        return array_map(function ($prompt) {
            return Forms\Components\Actions\Action::make($prompt['name'])
                ->badge()
                ->label($prompt['name'])
                ->action(fn (Forms\Set $set) => $set('prompt', $prompt['prompt']));
        }, $prompts);
    }

    public function submitPrompt()
    {
        $data = $this->form->getState();

        $this->question = true;
        $this->finished = false;
        $this->ai_response = '';

        // Con questo trick funziona bene, al posto di fare $this->generate()
        $this->js('$wire.generate()');
    }

    /**
     * @throws \Exception
     */
    public function generate(): void
    {
        $data = $this->form->getState();

        // Se è disabilitata la selezione del modello, sempre quello di default
        if (config('model-ai.disable_openai_model_selection')) {
            $data['ai_model'] = config('model-ai.default_openai_model');
        }

        $system_prompt = config('model-ai.system_prompt');

        $stream = \Postare\ModelAi\ModelAi::chat()->stream(
            $data['prompt'],
            $system_prompt,
            $data['context_data'],
            $data['ai_model'],
        );

        $this->ai_response = '';
        $this->finished = false;

        foreach ($stream as $response) {

            $content = $response->choices[0]->delta->content;

            $this->ai_response .= $content;

            if (is_null($content)) {
                // exit loop
                break;
            }

            $this->stream(to: 'ai_response', content: $content);
        }

        // Alla fine converte l'eventuale markdown in html
        $this->ai_response = str($this->ai_response)->markdown();
        $this->finished = true;
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('generate')
                ->label(__('filament-model-ai::model-ai.form.submit_prompt'))
                ->icon('heroicon-o-sparkles')
                ->action('submitPrompt'),
        ];
    }
}
