<?php

namespace Folklore\GraphQL\Support;

use Illuminate\Support\Fluent;

use GraphQL\Type\Definition\ObjectType;

class Type extends Fluent {
    
    public function attributes()
    {
        return [];
    }
    
    public function fields()
    {
        return [];
    }
    
    protected function getFieldResolver($name, $field)
    {
        if(isset($field['resolve']))
        {
            return $field['resolve'];
        }
        else if(method_exists($this, 'resolve'.studly_case($name).'Field'))
        {
            $resolver = array($this, 'resolve'.studly_case($name).'Field');
            return function() use ($resolver)
            {
                $args = func_get_args();
                return call_user_func_array($resolver, $args);
            };
        }
        
        return null;
    }
    
    public function getFields()
    {
        $fields = $this->fields();
        $allFields = [];
        foreach($fields as $name => $field)
        {
            if(is_string($field))
            {
                $allFields[$name] = app($field)->toArray();
            }
            else
            {
                $resolver = $this->getFieldResolver($name, $field);
                if($resolver)
                {
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
        $attributes = $this->attributes();
        $fields = $this->getFields();
        
        $attributes = array_merge($this->attributes, [
            'fields' => $fields
        ], $attributes);
        
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
