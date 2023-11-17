<?php

namespace Postare\ModelAi;

use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use OpenAI;

class ModelAi
{
    public OpenAI\Client $openai_client;

    public array $eloquent_model = [];

    public string $openai_model;

    public $system_prompt;

    public $user_prompt;

    public function __construct()
    {
        // Inizializza il client OpenAI
        $this->openai_client = OpenAI::client(config('model-ai.openai_api_key'));

        // Inizializza il modello OpenAI
        $this->openai_model = config('model-ai.default_openai_model');
    }

    public static function chat(): self
    {
        return new self();
    }

    // Metodo per impostare il prompt del sistema
    public function system(string $system_prompt): self
    {
        $this->system_prompt = $system_prompt;

        return $this;
    }

    // Metodo per impostare il modello OpenAI
    public function openai_model(string $model): self
    {
        $this->openai_model = $model;

        return $this;
    }

    /**
     * Set the Eloquent model to use.
     *
     * @param  string  $eloquent_model Nome della classe del modello Eloquent da utilizzare
     * @param  int  $id ID del record da utilizzare
     * @param  array  $select_data Dati da selezionare
     */
    public function model(string $eloquent_model, int $id, array $select_data = ['*']): self
    {
        $this->eloquent_model = [
            'class' => $eloquent_model,
            'id' => $id,
            'select_data' => $select_data,
        ];

        return $this;
    }

    // Metodo chat modificato per supportare catenazione
    public function prompt(string $prompt): self
    {
        $this->user_prompt = $prompt;

        return $this;
    }

    // Metodo per eseguire la richiesta
    public function send()
    {
        // Preparazione del payload
        $payload = [
            'model' => $this->openai_model,
            'messages' => [],
        ];

        // Se è impostato il prompt del sistema
        if ($this->system_prompt) {
            $payload['messages'][] = [
                'role' => 'system',
                'content' => $this->system_prompt,
            ];
        }

        if (! empty($this->eloquent_model)) {
            $model = $this->eloquent_model['class'];
            $id = $this->eloquent_model['id'];
            $select_data = $this->eloquent_model['select_data'];

            // Seleziona i dati dal modello Eloquent
            $model_data = $model::select($select_data)->where('id', $id)->first()->toJson(JSON_PRETTY_PRINT);

            // aggiungi i dati del modello al payload
            $payload['messages'][] = [
                'role' => 'system',
                'content' => "Consider the data at the end of this message as context and answer the questions I will ask you later. ``` $model_data ```",
            ];
        }

        // Se è impostato il prompt dell'utente
        if ($this->user_prompt) {
            $payload['messages'][] = [
                'role' => 'user',
                'content' => $this->user_prompt,
            ];
        }

        // Esegui la richiesta al servizio OpenAI
        $response = $this->openai_client->chat()->create($payload);

        return $response->choices[0]->message->content;
    }

    /**
     * Return list of models from OpenAI API. Only GPT* models are returned.
     */
    public function listModels(): array
    {
        return collect($this->openai_client->models()->list()->data)
            ->sortByDesc('created')
            ->pluck('id')
            ->filter(fn ($id) => Str::startsWith($id, 'gpt-'))
            ->mapWithKeys(function ($id) {
                return [$id => $id];
            })
            ->toArray();
    }

    /**
     * Stream response from OpenAI API.
     */
    public function stream(
        string $prompt,
        string $system_prompt,
        string $model_data_json,
        string $model = 'gpt-3.5-turbo'
    ): ?object {
        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "$system_prompt ``` $model_data_json ```",
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ];

        try {

            return $this->openai_client->chat()->createStreamed($payload);

        } catch (\Exception $e) {
            $this->handleException($e);

            return null;
        }
    }

    /**
     * Handle exception from OpenAI API call.
     */
    protected function handleException(\Exception $e): void
    {
        Notification::make()
            ->title('OpenAI Error:')
            ->body($e->getMessage())
            ->danger()
            ->persistent()
            ->send();
    }
}
