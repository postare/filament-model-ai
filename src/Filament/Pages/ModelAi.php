<?php

namespace Postare\ModelAi\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\Page;

class ModelAi extends Page
{
    use InteractsWithForms;

    public ?array $data = [];

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament-model-ai::filament.pages.model-ai';

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|mixed
     */
    private mixed $model;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|mixed
     */
    private mixed $selected_columns;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|mixed
     */
    private mixed $field_label;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|mixed
     */
    private mixed $field_id;

    private array $predefined_prompts_actions;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|mixed
     */
    private mixed $open_ai_model;

    public function __construct()
    {
        $this->open_ai_model = config('model-ai.open_ai_model',
            [
                'gpt-3.5-turbo-1106' => 'Updated GPT 3.5 Turbo', // predefined first
                'gpt-4-1106-preview' => 'GPT-4 Turbo',
            ],
        );

        $this->model = config('model-ai.laravel_model',
            \App\Models\User::class
        );

        $this->selected_columns = config('model-ai.selected_columns',
            ['name', 'email']
        );

        $this->field_label = config('model-ai.field_label',
            'name'
        );

        $this->field_id = config('model-ai.field_id',
            'id'
        );

        $this->predefined_prompts_actions = $this->predefinedPromptsToActions(config('model-ai.predefined_prompts'));
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
                    ->label('Elemento')
                    ->live(false, 500)
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => $this->model::where($this->field_label, 'like', "%{$search}%")->limit(50)->pluck($this->field_label, $this->field_id)->toArray())
                    ->afterStateUpdated(function (Set $set, $state) {
                        $data = $this->model::select($this->selected_columns)->where($this->field_id, $state)->first();
                        $set('data', $data->toJson(JSON_PRETTY_PRINT));
                    })
                    ->required(),

                Forms\Components\Select::make('ai_model')
                    ->label('Modello')
                    ->options($this->open_ai_model)
                    ->required(),

                Forms\Components\Textarea::make('data')
                    ->hidden(fn (Get $get) => ! $get('item'))
                    ->label('Dati')
                    ->tap()
                    ->required(),

                Forms\Components\Textarea::make('prompt')
                    ->label('Inserisci la tua richiesta')
                    ->rows(3)
                    ->placeholder('Descrivi il contenuto desiderato, inserisci il tuo comando o le tue domande qui...')
                    ->live()
                    ->required()
                    ->helperText('Al prompt verranno aggiunti automaticamente i dati dell\'immobile selezionato')
                    ->columnSpanFull(),

                Forms\Components\Fieldset::make('Prompt predefiniti')
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
        $actions = [];

        if (is_array($prompts)) {
            foreach ($prompts as $prompt) {
                $actions[] = \Filament\Forms\Components\Actions\Action::make($prompt['name'])
                    ->badge()
                    ->label($prompt['name'])
                    ->action(fn (Set $set) => $set('prompt', $prompt['prompt']));
            }
        }

        return $actions;
    }

    public function save(): void
    {
        //
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->submit('save'),
        ];
    }
}
