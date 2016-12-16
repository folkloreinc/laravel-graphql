<?php

namespace Folklore\GraphQL\Support;

use Illuminate\Support\Fluent;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InterfaceType;

class Type extends Fluent
{
    protected static $instances = [];
    
    protected $fields = [];
    protected $interfaces = [];
    
    public function fields()
    {
        return [];
    }
    
    public function interfaces()
    {
        return [];
    }
    
    public function attributes()
    {
        return [];
    }
    
    public function getInterfaces()
    {
        return array_merge($this->interfaces, $this->interfaces());
    }
    
    public function getFields()
    {
        return array_merge($this->fields, $this->fields());
    }
    
    public function setFields($fields)
    {
        $this->fields = $fields;
    }
    
    public function setInterfaces($interfaces)
    {
        $this->interfaces = $interfaces;
    }
    
    protected function getFieldResolver($name, $field)
    {
        $resolveMethod = 'resolve'.studly_case($name).'Field';
        if (isset($field['resolve'])) {
            return $field['resolve'];
        } elseif (method_exists($this, $resolveMethod)) {
            $resolver = array($this, $resolveMethod);
            return function () use ($resolver) {
                $args = func_get_args();
                return call_user_func_array($resolver, $args);
            };
        }
        
        return null;
    }
    
    public function getFieldsForObjectType()
    {
        $fields = $this->getFields();
        $allFields = [];
        foreach ($fields as $name => $field) {
            if (is_string($field)) {
                $field = app($field);
                $field->name = $name;
                $allFields[$name] = $field->toArray();
            } else {
                $resolver = $this->getFieldResolver($name, $field);
                if ($resolver) {
                    $field['resolve'] = $resolver;
                }
                $allFields[$name] = $field;
            }
        }
        
        return $allFields;
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
        
        $attributes['fields'] = function () {
            return $this->getFieldsForObjectType();
        };
        
        $interfaces = $this->getInterfaces();
        if (sizeof($interfaces)) {
            $attributes['interfaces'] = $interfaces;
        }
        
        return $attributes;
    }
    
    public function toType()
    {
        return new ObjectType($this->toArray());
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
