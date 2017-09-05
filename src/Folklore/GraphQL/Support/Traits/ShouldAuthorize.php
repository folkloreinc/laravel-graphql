<?php

namespace Folklore\GraphQL\Support\Traits;

use Folklore\GraphQL\Error\AuthorizationError;
use Closure;

trait ShouldAuthorize
{
    public function getResolver()
    {
        $resolver = parent::getResolver();
        if (!$resolver) {
            return null;
        }

        $authorize = array_get($this->attributes, 'authorize');
        if (is_null($authorize) && method_exists($this, 'authorize')) {
            $authorize = array($this, 'authorize');
        }

        return function () use ($resolver, $authorize) {
            $arguments = func_get_args();

            if (!is_null($authorize) && call_user_func_array($authorize, $arguments) !== true) {
                throw new AuthorizationError('Unauthorized');
            }

            return call_user_func_array($resolver, $arguments);
        };
    }
}
