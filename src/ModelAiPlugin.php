<?php

namespace Postare\ModelAi;

use Filament\Contracts\Plugin;
use Filament\Panel;

class ModelAiPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-model-ai';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            \Postare\ModelAi\Filament\Pages\ModelAi::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
