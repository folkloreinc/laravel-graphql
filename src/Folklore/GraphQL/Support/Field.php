<?php

namespace Folklore\GraphQL\Support;

use Folklore\GraphQL\Error\AuthorizationError;
use Folklore\GraphQL\Error\ValidationError;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\WrappingType;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Validator;

/**
 * This is a generic class for fields.
 *
 * It should only be used directly for custom fields, if you want to have
 * a Query or Mutation, extend those classes instead.
 *
 * Class Field
 * @package Folklore\GraphQL\Support
 */
abstract class Field extends Fluent
{
    /**
     * Override this in your queries or mutations to provide custom authorization.
     *
     * @param $root
     * @param $args
     *
     * @return bool
     */
    public function authorize($root, $args)
    {
        // Default to allowing authorization
        return true;
    }

    /**
     * Override this in your queries or mutations to authenticate per query or mutation.
     *
     * @param $root
     * @param $args
     * @param $context
     *
     * @return bool
     */
    public function authenticated($root, $args, $context)
    {
        // Default to allow authentication
        return true;
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
     * Get the attributes from the container.
     *
     * @return array
     */
    public function getAttributes()
    {
        return array_merge(
            $this->attributes,
            [
                'args' => $this->args(),
                'type' => $this->type(),
                'resolve' => $this->getResolver(),
            ]
        );
    }

    /**
     * Define the arguments expected by the field.
     *
     * @return array
     */
    public function args()
    {
        return [];
    }

    /**
     * Define a GraphQL Type which is returned by this field.
     *
     * @return \Folklore\GraphQL\Support\Type
     */
    public abstract function type();

    /**
     * Returns a function that wraps the resolve function with authentication and validation checks.
     *
     * @return \Closure
     */
    protected function getResolver()
    {
        return function () {
            $resolutionArguments = func_get_args();

            // Check authentication first
            if (call_user_func_array([$this, 'authenticated'], $resolutionArguments) !== true) {
                throw new AuthorizationError('Unauthenticated');
            }

            // After authentication, check specific authorization
            if (call_user_func_array([$this, 'authorize'], $resolutionArguments) !== true) {
                throw new AuthorizationError('Unauthorized');
            }

            call_user_func_array([$this, 'validate'], $resolutionArguments);


            return call_user_func_array([$this, 'resolve'], $resolutionArguments);
        };
    }

    /**
     * Return a result for the field.
     *
     * @param $root
     * @param $args
     * @param $context
     * @param ResolveInfo $info
     * @return mixed
     */
    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        // Use the default resolver, can be overridden in custom Fields, Queries or Mutations
        // This enables us to still apply authentication, authorization and validation upon the query
        return call_user_func(config('graphql.defaultFieldResolver'));
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        $attributes = $this->getAttributes();
        return isset($attributes[$key]) ? $attributes[$key] : null;
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        $attributes = $this->getAttributes();
        return isset($attributes[$key]);
    }

    /**
     * Overwrite rules at any part in the tree of field arguments.
     *
     * The rules defined in here take precedence over the rules that are
     * defined inline or inferred from nested Input Objects.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Gather all the rules and throw if invalid.
     */
    protected function validate()
    {
        $resolutionArguments = func_get_args();
        $inputArguments = array_get($resolutionArguments, 1, []);

        $argumentRules = $this->getRulesForArguments($this->args(), $inputArguments);
        $explicitRules = call_user_func_array([$this, 'rules'], $resolutionArguments);
        $argumentRules = array_merge($argumentRules, $explicitRules);

        foreach ($argumentRules as $key => $rules) {
            $resolvedRules[$key] = $this->resolveRules($rules, $resolutionArguments);
        }

        if (isset($resolvedRules)) {
            $validationErrorMessages = call_user_func_array([$this, 'validationErrorMessages'], $resolutionArguments);
            $validator = $this->getValidator($inputArguments, $resolvedRules, $validationErrorMessages);
            if ($validator->fails()) {
                throw (new ValidationError('validation'))->setValidator($validator);
            }
        }
    }

    /**
     * Get the combined explicit and type-inferred rules.
     *
     * @param $argDefinitions
     * @param $argValues
     * @return array
     */
    protected function getRulesForArguments($argDefinitions, $argValues)
    {
        $rules = [];

        foreach ($argValues as $name => $value) {
            $definition = $argDefinitions[$name];

            $typeAndKey = $this->unwrapType($definition['type'], $name);
            $key = $typeAndKey['key'];
            $type = $typeAndKey['type'];

            // Get rules that are directly defined on the field
            if (isset($definition['rules'])) {
                $rules[$key] = $definition['rules'];
            }

            if ($type instanceof InputObjectType) {
                $rules = array_merge($rules, $this->getRulesFromInputObjectType($key, $type, $value));
            }
        }

        return $rules;
    }

    /**
     * @param $type
     * @param $key
     * @return array
     */
    protected function unwrapType($type, $key)
    {
        // Unpack until we get to the root type
        while ($type instanceof WrappingType) {
            if ($type instanceof ListOfType) {
                // Add this to the prefix to allow Laravel validation for arrays
                $key .= '.*';
            }

            $type = $type->getWrappedType();
        }

        return [
            'type' => $type,
            'key' => $key,
        ];
    }

    protected function getRulesFromInputObjectType($parentKey, InputObjectType $inputObject, $values)
    {
        $rules = [];
        // At this point we know we expect InputObjects, but there might be more then one value
        // Since they might have different fields, we have to look at each of them individually
        // If we have string keys, we are dealing with the values themselves
        if ($this->hasStringKeys($values)) {
            foreach ($values as $name => $value) {
                $key = "{$parentKey}.{$name}";

                $field = $inputObject->getFields()[$name];

                $typeAndKey = $this->unwrapType($field->type, $key);
                $key = $typeAndKey['key'];
                $type = $typeAndKey['type'];

                if (isset($field->rules)) {
                    $rules[$key] = $field->rules;
                }

                if ($type instanceof InputObjectType) {
                    // Recursively call the parent method to get nested rules, passing in the new prefix
                    $rules = array_merge($rules, $this->getRulesFromInputObjectType($key, $type, $value));
                }
            }
        } else {
            // Go one level deeper so we deal with actual values
            foreach ($values as $nestedValues) {
                $rules = array_merge($rules, $this->getRulesFromInputObjectType($parentKey, $inputObject, $nestedValues));
            }
        }

        return $rules;
    }

    /**
     * @param array $array
     * @return bool
     */
    protected function hasStringKeys(array $array)
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    /**
     * @param $rules
     * @param $arguments
     * @return mixed
     */
    protected function resolveRules($rules, $arguments)
    {
        // Rules can be defined as closures that are passed the resolution arguments
        if (is_callable($rules)) {
            return call_user_func_array($rules, $arguments);
        }

        return $rules;
    }

    /**
     * @param $args
     * @param $rules
     * @param array $messages
     *
     * @return Validator
     */
    protected function getValidator($args, $rules, $messages = [])
    {
        /** @var Validator $validator */
        $validator = app('validator')->make($args, $rules, $messages);
        if (method_exists($this, 'withValidator')) {
            $this->withValidator($validator, $args);
        }

        return $validator;
    }

    /**
     * Return an array of custom validation error messages.
     *
     * @return array
     */
    protected function validationErrorMessages($root, $args, $context)
    {
        return [];
    }
}
