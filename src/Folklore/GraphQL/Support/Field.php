<?php

namespace Folklore\GraphQL\Support;

use Illuminate\Support\Fluent;
use Folklore\GraphQL\Error\AuthorizationError;

class Field extends Fluent
{
    /**
     * Override this in your queries or mutations
     * to provide custom authorization
     */
    public function authorize($root, $args)
    {
        return true;
    }

    /**
     * Override this in your queries or mutations
     * to authenticate per query or mutation
     */
    public function authenticated($root, $args, $context)
    {
        return true;
    }

    /**
     * Message of unauthorized error
     *
     * @return string
     */
    protected function unauthorized()
    {
        return 'Unauthorized';
    }

    /**
     * Message of unauthenticated error
     *
     * @return string
     */
    protected function unauthenticated()
    {
        return 'Unauthenticated';
    }

    public function attributes()
    {
        return [];
    }

    public function type()
    {
        return null;
    }

    public function args()
    {
        return [];
    }

    protected function getResolver()
    {
        if (!method_exists($this, 'resolve')) {
            return null;
        }

        $resolver = array($this, 'resolve');
        $authenticate = [$this, 'authenticated'];
        $authorize = [$this, 'authorize'];

        return function () use ($resolver, $authorize, $authenticate) {
            $args = func_get_args();

            // Authenticated
            if (call_user_func_array($authenticate, $args) !== true) {
                throw new AuthorizationError($this->unauthenticated());
            }

            // Authorize
            if (call_user_func_array($authorize, $args) !== true) {
                throw new AuthorizationError($this->unauthorized());
            }

            return call_user_func_array($resolver, $args);
        };
    }

    /**
     * Get the attributes from the container.
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = $this->attributes();
        $args = $this->args();

        $attributes = array_merge($this->attributes, [
            'args' => $args
        ], $attributes);

        $type = $this->type();
        if (isset($type)) {
            $attributes['type'] = $type;
        }

        $resolver = $this->getResolver();
        if (isset($resolver)) {
            $attributes['resolve'] = $resolver;
        }

        return $attributes;
    }

    /**
     * Convert the Fluent instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getAttributes();
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        $attributes = $this->getAttributes();
        return isset($attributes[$key]) ? $attributes[$key]:null;
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param  string  $key
     * @return void
     */
    public function __isset($key)
    {
        $attributes = $this->getAttributes();
        return isset($attributes[$key]);
    }
}
