<?php

namespace Postare\ModelAi\Actions;

use Filament\Notifications\Notification;
use OpenAI;

class Ai
{
    public OpenAI\Client $client;

    public function __construct()
    {
        // Inizializza il client OpenAI
        $this->client = OpenAI::client(config('model-ai.openai_api_key'));
    }

    public static function make(): self
    {
        return new self();
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
                    'content' => $system_prompt.' Considera i dati alla fine di questo messaggio come contesto e rispondi alle domande che ti farò successivamente: '.$model_data_json,
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ];

        try {

            return $this->client->chat()->createStreamed($payload);

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
