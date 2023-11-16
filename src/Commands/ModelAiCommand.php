<?php

namespace Postare\ModelAi\Commands;

use Illuminate\Console\Command;

class ModelAiCommand extends Command
{
    public $signature = 'filament-model-ai';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
