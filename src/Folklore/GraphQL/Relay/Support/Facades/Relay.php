<?php

namespace Folklore\GraphQL\Relay\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Relay extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'graphql.relay';
    }
}
