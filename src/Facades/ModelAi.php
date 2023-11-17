<?php

namespace Postare\ModelAi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Postare\ModelAi\ModelAi
 */
class ModelAi extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Postare\ModelAi\ModelAi::class;
    }
}
