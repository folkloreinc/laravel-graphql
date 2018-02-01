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

    /**
     * Return an array of custom validation error messages.
     *
     * @return array
     */
    public function validationErrorMessages($root, $args, $context)
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
                $argsRules[$name] = $this->resolveRules($arg['rules'], $arguments);
            }

            if (isset($arg['type'])) {
                $argsRules = array_merge($argsRules, $this->inferRulesFromType($arg['type'], $name, $arguments));
            }
        }

        return array_merge($rules, $argsRules);
    }

    public function resolveRules($rules, $arguments)
    {
        if (is_callable($rules)) {
            return call_user_func_array($rules, $arguments);
        }

        return $rules;
    }

    public function inferRulesFromType($type, $prefix, $resolutionArguments)
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
            $rules = array_merge($rules, $this->getInputTypeRules($type, $prefix, $resolutionArguments));
        }

        // Ignore scalar types

        return $rules;
    }

    public function getInputTypeRules(InputObjectType $input, $prefix, $resolutionArguments)
    {
        $rules = [];

        foreach ($input->getFields() as $name => $field) {
            $key = "{$prefix}.{$name}";

            // get any explicitly set rules
            if (isset($field->rules)) {
                $rules[$key] = $this->resolveRules($field->rules, $resolutionArguments);
            }

            // then recursively call the parent method to see if this is an
            // input object, passing in the new prefix
            $rules = array_merge($rules, $this->inferRulesFromType($field->type, $key, $resolutionArguments));
        }

        return $rules;
    }

    protected function getValidator($args, $rules, $messages = [])
    {
        $validator =  app('validator')->make($args, $rules, $messages);
        if (method_exists($this, 'withValidator')) {
            $this->withValidator($validator, $args);
        }

        return $validator;
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
            $validationErrorMessages = call_user_func_array([$this, 'validationErrorMessages'], $arguments);
            if (sizeof($rules)) {
                $args = array_get($arguments, 1, []);
                $validator = $this->getValidator($args, $rules, $validationErrorMessages);
                if ($validator->fails()) {
                    throw with(new ValidationError('validation'))->setValidator($validator);
                }
            }

            return call_user_func_array($resolver, $arguments);
        };
    }
}
