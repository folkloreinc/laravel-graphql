<?php

namespace Folklore\GraphQL\Support\Traits;

use Folklore\GraphQL\Error\ValidationError;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\WrappingType;

trait ShouldValidate
{
    protected function rules()
    {
        return [];
    }

    public function getRules()
    {
        $arguments = func_get_args();

        $rules = call_user_func_array([$this, 'rules'], $arguments);
        $argsRules = [];
        foreach ($this->args() as $name => $arg) {
            if (isset($arg['rules'])) {
                if (is_callable($arg['rules'])) {
                    $argsRules[$name] = call_user_func_array($arg['rules'], $arguments);
                } else {
                    $argsRules[$name] = $arg['rules'];
                }
            }

            if (isset($arg['type'])) {
                $argsRules = array_merge($argsRules, $this->inferRulesFromType($arg['type'], $name));
            }
        }

        return array_merge($rules, $argsRules);
    }

    public function inferRulesFromType($type, $prefix)
    {
        $rules = [];

        // if it is an array type, add an array validation component
        if ($type instanceof ListOfType) {
            $prefix = "{$prefix}.*";
        }

        // make sure we are dealing with the actual type
        if ($type instanceof WrappingType) {
            $type = $type->getWrappedType();
        }

        // if it is an input object type - the only type we care about here...
        if ($type instanceof InputObjectType) {
            // merge in the input type's rules
            $rules = array_merge($rules, $this->getInputTypeRules($type, $prefix));
        }

        // Ignore scalar types

        return $rules;
    }

    public function getInputTypeRules(InputObjectType $input, $prefix)
    {
        $rules = [];

        foreach ($input->getFields() as $name => $field) {
            $key = "{$prefix}.{$name}";

            // get any explicitly set rules
            if (isset($field->rules)) {
                $rules[$key] = $field->rules;
            }

            // then recursively call the parent method to see if this is an
            // input object, passing in the new prefix
            array_merge($rules, $this->inferRulesFromType($field->type, $key));
        }

        return $rules;
    }

    protected function getValidator($args, $rules)
    {
        return app('validator')->make($args, $rules);
    }

    protected function getResolver()
    {
        $resolver = parent::getResolver();
        if (!$resolver) {
            return null;
        }

        return function () use ($resolver) {
            $arguments = func_get_args();

            $rules = call_user_func_array([$this, 'getRules'], $arguments);
            if (sizeof($rules)) {
                $args = array_get($arguments, 1, []);
                $validator = $this->getValidator($args, $rules);
                if ($validator->fails()) {
                    throw with(new ValidationError('validation'))->setValidator($validator);
                }
            }

            return call_user_func_array($resolver, $arguments);
        };
    }
}
