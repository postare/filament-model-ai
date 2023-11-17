# Laravel ModelAI Plugin

ModelAI is a plugin designed for [FilamentPHP](https://filamentphp.com/) that interfaces with OpenAI APIs. Its primary function is to use existing model data as context to formulate requests to OpenAI APIs. In practice, it allows users to select specific records from a data model, such as a list of real estate properties or e-commerce products, and use this information as a basis for generating intelligent content or responses through OpenAI's AI capabilities.

## Examples of Use

### Example 1: Real Estate Portal

In a real estate portal, ModelAI can utilize data from a 'Properties' model to create tailored marketing content. For instance, by selecting a specific property, the plugin can generate optimized social media posts or identify key property-related points, all based on the specific property's data.

### Example 2: Wine E-commerce

In a wine e-commerce platform, the plugin could be configured to work with a 'Wine' model, containing details like variety, vintage, and tasting notes. Using this data, 'ModelAI' could generate engaging product descriptions, gastronomic pairing recommendations, or even educational content about wine culture, all while harnessing OpenAI's artificial intelligence to ensure high-quality and relevant content.

## Installation

You can easily install the package via Composer:

```bash
composer require postare/filament-model-ai
```

Once the package is installed, you need to add your OpenAI API Key to your `.env` file:

```bash
OPENAI_API_KEY=sk-...
```

To publish the configuration file, use the following command:

```bash
php artisan vendor:publish --tag="filament-model-ai-config"
```

Optionally, you can also publish the views:

```bash
php artisan vendor:publish --tag="filament-model-ai-views"
```

The published configuration file contains the following settings:

```php
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
    'field_label' => 'name', // Field to use as a label
    'field_id' => 'id', // Field to use as an ID

    // System Prompt
    'system_prompt' => 'You are a helpful assistant. Consider the data at the end of this message as context and answer the questions I will ask you later.',

    // Predefined Prompts (feel free to add, remove, or modify them)
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
```

Feel free to customize these settings to fit your specific use case. Enjoy using ModelAI with FilamentPHP!

## Usage

In addition to a page in your Panel where you can select the desired element, the Model, and the Prompt, you can also use ModelAI elsewhere in your project.

```php
$response = ModelAi::chat()

  // Optional: Override the default OpenAI model specified in the configuration file
  ->openai_model('gpt-4-1106-preview')

  // Optional
  ->system('your name is GeePeeTee, and you speak in a very 16th-century, very polished way.')

  // Optional, model to use:
  // params: model, id, selected_columns
  ->model(\App\Models\User::class, 1, ['name', 'email', 'email_verified_at'])

  // Mandatory, prompt to use:
  ->prompt("tell me about this user")

  // Mandatory, send the request
  ->send();

// Pray tell, thou hath sought to gain insight into the individual known by the appellation "Francesco". This gentle soul didst establish an entity of digital correspondence through "inerba@******.com", yet verily, the verification of such an electronic missive remains a quest unfulfilled.

$response = ModelAi::chat()
  ->prompt("make up your own dad joke")
  ->send();

// Why don't skeletons fight each other? They don't have the guts.
```

## Features
- [x] OpenAI API Integration
- [x] Customizable Models
- [x] Customizable Prompts	
- [x] FilamentPHP Page
