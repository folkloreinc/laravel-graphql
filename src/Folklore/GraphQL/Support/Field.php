<?php

namespace Folklore\GraphQL\Support;

use Illuminate\Support\Fluent;

class Field extends Fluent
{
    protected $type = null;
    protected $resolver = null;
    protected $args = [];
    
    public function type()
    {
        return null;
    }
    
    public function attributes()
    {
        return [];
    }
    
    public function args()
    {
        return [];
    }
    
    public function getType()
    {
        $type = $this->type();
        return $type ? $type:$this->type;
    }
    
    public function setType($args)
    {
        $this->args = $args;
    }
    
    public function getArgs()
    {
        return array_merge($this->args, $this->args());
    }
    
    public function setArgs($args)
    {
        $this->args = $args;
    }
    
    protected function getResolver()
    {
        if ($this->resolver) {
            return $this->resolver;
        }
        
        if (!method_exists($this, 'resolve')) {
            return null;
        }
        
        $resolver = array($this, 'resolve');
        return function () use ($resolver) {
            $args = func_get_args();
            return call_user_func_array($resolver, $args);
        };
    }
    
    public function setResolver($resolver)
    {
        return $this->resolver;
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
