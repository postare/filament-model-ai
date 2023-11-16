# Integrate AI in FilamentPHP, leveraging model data for smart commands.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/postare/filament-model-ai.svg?style=flat-square)](https://packagist.org/packages/postare/filament-model-ai)
[![Total Downloads](https://img.shields.io/packagist/dt/postare/filament-model-ai.svg?style=flat-square)](https://packagist.org/packages/postare/filament-model-ai)

## Installation

You can install the package via composer:

```bash
composer require postare/filament-model-ai
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-model-ai-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-model-ai-views"
```

This is the contents of the published config file:

```php
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
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Francesco Apruzzese](https://github.com/postare)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
