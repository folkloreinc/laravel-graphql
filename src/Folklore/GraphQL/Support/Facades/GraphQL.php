<?php namespace Folklore\GraphQL\Support\Facades\Facade;

use Illuminate\Support\Facades\Facade;

class GraphQL extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'graphql';
    }
}
