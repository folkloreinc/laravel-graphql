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
    protected function authorize()
    {
        return true;
    }

    protected function type()
    {
        return null;
    }

    protected function attributes()
    {
        return [];
    }

    protected function args()
    {
        return [];
    }

    public function getType()
    {
        $type = array_get($this->attributes, 'type');
        return $type ? $type:$this->type();
    }

    public function setType($type)
    {
        $this->attributes['type'] = $type;
        return $this;
    }

    public function getArgs()
    {
        $args = array_get($this->attributes, 'args');
        return $args ? $args:$this->args();
    }

    public function setArgs($args)
    {
        $this->attributes['args'] = $args;
        return $this;
    }

    public function getRootResolver()
    {
        $resolver = array_get($this->attributes, 'rootResolver');
        if (!$resolver && method_exists($this, 'resolveRoot')) {
            $resolver = array($this, 'resolveRoot');
        }
        return $resolver;
    }

    public function setRootResolver($resolver)
    {
        $this->attributes['rootResolver'] = $resolver;
        return $this;
    }

    /**
     * Get the resolver of this field. If a resolver was set with the setResolver
     * method, it will be used, otherwise it will use the resolve method (if present).
     * This method wraps the resolver in a closure.
     *
     * @return Closure
     */
    public function getResolver()
    {
        $resolver = array_get($this->attributes, 'resolver');
        if (!$resolver && method_exists($this, 'resolve')) {
            $resolver = array($this, 'resolve');
        }

        if (!$resolver) {
            return null;
        }

        $authorize = array_get($this->attributes, 'authorize');
        if (is_null($authorize) && method_exists($this, 'authorize')) {
            $authorize = array($this, 'authorize');
        }

        $rootResolver = $this->getRootResolver();

        return function () use ($authorize, $rootResolver, $resolver) {
            if ($authorize && call_user_func($authorize) !== true) {
                throw new AuthorizationError('Unauthorized');
            }

            $args = func_get_args();
            if ($rootResolver) {
                $root = call_user_func_array($rootResolver, $args);
                if ($root === null) {
                    return null;
                }
                $args[0] = $root;
            }
            return call_user_func_array($resolver, $args);
        };
    }

    /**
     * Set the resolver that will be used to resolve this field.
     *
     * @param callable|null $resolver The callable that will be called on resolve
     * @return $this
     */
    public function setResolver($resolver)
    {
        $this->attributes['resolver'] = $resolver;
        return $this;
    }

    /**
     * Get the attributes from the container.
     *
     * @return array
     */
    public function getAttributes()
    {
        return array_merge($this->attributes, $this->attributes());
    }

    /**
     * Convert the Fluent instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = $this->getAttributes();

        $args = $this->getArgs();
        if (sizeof($args)) {
            $attributes['args'] = $args;
        }

        $type = $this->getType();
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
