<?php

namespace Postare\ModelAi\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Postare\ModelAi\Actions\Ai;

class ModelAi extends \Filament\Pages\Page
{
    use InteractsWithForms;

    public ?array $data = [];

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

    private string $model;

    private array $selected_columns;

    private string $field_label;

    private string $field_id;

    private array $predefined_prompts_actions;

    private array $open_ai_model;

    public $ai_response;

    public bool $finished = false;

    public bool $question = false;

    private mixed $system_prompt;

    public function __construct()
    {
        $this->initializeConfigurations();
    }

    public function mount(): void
    {
        $this->form->fill([
            'ai_model' => array_key_first($this->open_ai_model),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('item')
                    ->label(__('filament-model-ai::model-ai.form.item'))
                    ->live(false, 500)
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => $this->model::where($this->field_label, 'like', "%{$search}%")->limit(50)->pluck($this->field_label, $this->field_id)->toArray())
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        $data = $this->model::select($this->selected_columns)->where($this->field_id, $state)->first();
                        $set('data', $data->toJson(JSON_PRETTY_PRINT));
                    })
                    ->required(),

                Forms\Components\Select::make('ai_model')
                    ->label(__('filament-model-ai::model-ai.form.ai_model'))
                    ->options($this->open_ai_model)
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
                    ->hidden(count($this->predefined_prompts_actions) == 0)
                    ->schema([
                        Forms\Components\Actions::make($this->predefined_prompts_actions)->columnSpanFull(),
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

        $stream = Ai::make()->stream(
            $data['prompt'],
            $this->system_prompt,
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

    /**
     * Initialize configurations from config file
     */
    private function initializeConfigurations(): void
    {
        $this->open_ai_model = config(
            'model-ai.openai_models',
            [
                'gpt-3.5-turbo-1106' => 'Updated GPT 3.5 Turbo', // predefined first
                'gpt-4-1106-preview' => 'GPT-4 Turbo',
            ],
        );

        $this->model = config(
            'model-ai.laravel_model',
            \App\Models\User::class
        );

        $this->selected_columns = config(
            'model-ai.selected_columns',
            ['name', 'email']
        );

        $this->field_label = config(
            'model-ai.field_label',
            'name'
        );

        $this->system_prompt = config(
            'model-ai.system_prompt',
            'You are a helpful assistant'
        );

        $this->field_id = config(
            'model-ai.field_id',
            'id'
        );

        $this->predefined_prompts_actions = $this->predefinedPromptsToActions(config('model-ai.predefined_prompts'));

    }
}
