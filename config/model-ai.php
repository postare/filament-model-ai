<?php

// Configuration for Postare/ModelAi
return [
    // OpenAI API Key
    'openai_api_key' => env('OPENAI_API_KEY', ''),

    // Default OpenAI Model (refer to https://platform.openai.com/docs/models)
    'default_openai_model' => 'gpt-3.5-turbo-1106',

    // Disable selecting OpenAI Model
    'disable_openai_model_selection' => false,

    // Slug for the Model AI page
    'slug' => 'model-ai',

    // Filament Navigation Group (refer to translation file for label or disable it)
    'use_navigation_group' => true,

    // Model Settings
    'eloquent_model' => \App\Models\User::class, // Laravel model to use
    'selected_columns' => [ // Selected columns from the model
        'name',
        'email',
    ],
    'field_label' => 'name', // Field to use as label
    'field_id' => 'id', // Field to use as ID

    // System Prompt
    'system_prompt' => 'You are a helpful assistant. Consider the data at the end of this message as context and answer the questions I will ask you later.',

    // Predefined Prompts (feel free to add, remove or modify them)
    'predefined_prompts' => [
        [
            'name' => 'SEO', // Prompt name
            'prompt' => 'Generate a title and an SEO-oriented meta description based on the provided data.', // Prompt instruction
        ],
        [
            'name' => 'Facebook Post', // Prompt name
            'prompt' => 'Create a Facebook post, avoiding lists.', // Prompt instruction
        ],
    ],
];
