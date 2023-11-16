<?php

// config for Postare/ModelAi
return [
    // Api
    'openai_api_key' => env('OPENAI_API_KEY', ''),

    // OpenAI Models (see https://platform.openai.com/docs/models)
    'openai_models' => [
        'gpt-3.5-turbo-1106' => 'Updated GPT 3.5 Turbo', // predefined first
        'gpt-4-1106-preview' => 'GPT-4 Turbo',
    ],

    // Filament Navigation Group, see translation file for label or disable it
    'use_navigation_group' => true,

    // Model Settings
    'laravel_model' => \App\Models\User::class,
    'selected_columns' => [
        'name',
        'email',
    ],
    'field_label' => 'name',
    'field_id' => 'id',

    'system_prompt' => 'You are a helpful assistant',

    // Predefined Prompts, feel free to add or remove or change them
    'predefined_prompts' => [
        [
            'name' => 'SEO',
            'prompt' => 'generate a title and an SEO-oriented meta_description based on the provided data',
        ],
        [
            'name' => 'Facebook Post',
            'prompt' => 'create a Facebook post, avoiding lists',
        ],
    ],
];
