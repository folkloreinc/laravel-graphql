<?php

namespace Folklore\GraphQL\Support;

use Folklore\GraphQL\Error\AuthorizationError;
use Folklore\GraphQL\Error\ValidationError;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\WrappingType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Fluent;

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
     * Validation rules for the Field arguments.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * How many levels of nesting should validation be applied to.
     *
     * @var int
     */
    protected $validationDepth = 10;

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
     * Returns a function that wraps the resolve function with authentication and validation checks.
     *
     * @return \Closure
     */
    protected function getResolver()
    {
        $authenticated = [$this, 'authenticated'];
        $authorize = [$this, 'authorize'];
        $resolve = [$this, 'resolve'];

        return function () use ($authenticated, $authorize, $resolve) {
            $args = func_get_args();

            // Check authentication first
            if (call_user_func_array($authenticated, $args) !== true) {
                throw new AuthorizationError('Unauthenticated');
            }

            // After authentication, check specific authorization
            if (call_user_func_array($authorize, $args) !== true) {
                throw new AuthorizationError('Unauthorized');
            }

            // Apply additional validation
            $rules = call_user_func_array([$this, 'getRules'], $args);
            if (sizeof($rules)) {
                $validationErrorMessages = call_user_func_array([$this, 'validationErrorMessages'], $args);
                $inputArguments = array_get($args, 1, []);
                $validator = $this->getValidator($inputArguments, $rules, $validationErrorMessages);
                if ($validator->fails()) {
                    throw with(new ValidationError('validation'))->setValidator($validator);
                }
            }

            return call_user_func_array($resolve, $args);
        };
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
     * Get the combined explicit and type-inferred rules.
     *
     * @return array
     */
    public function getRules()
    {
        $arguments = func_get_args();

        $argsRules = $this->getRulesFromArgs($this->args(), $this->validationDepth, null, $arguments);

        // Merge rules that were set separately with those defined through args
        $explicitRules = call_user_func_array([$this, 'rules'], $arguments);
        return array_merge($argsRules, $explicitRules);
    }

    /**
     * @param $fields
     * @param $inputValueDepth
     * @param $parentKey
     * @param $resolutionArguments
     * @return array
     */
    protected function getRulesFromArgs($fields, $inputValueDepth, $parentKey, $resolutionArguments)
    {
        // At depth 0, there are still field rules to gather
        // once we get below 0, we have gathered all the rules necessary
        // for the nesting depth
        if ($inputValueDepth < 0) {
            return [];
        }

        // We are going one level deeper
        $inputValueDepth--;

        $rules = [];
        // Merge in the rules of the Input Type
        foreach ($fields as $fieldName => $field) {
            // Count the depth per field
            $fieldDepth = $inputValueDepth;

            // If given, add the parent key
            $key = $parentKey ? "{$parentKey}.{$fieldName}" : $fieldName;

            // The values passed in here may be of different types, depending on where they were defined
            if ($field instanceof InputObjectField) {
                // We can depend on type being set, since a field without type is not valid
                $type = $field->type;
                // Rules are optional so they may not be set
                $fieldRules = isset($field->rules) ? $field->rules : [];
            } else {
                $type = $field['type'];
                $fieldRules = isset($field['rules']) ? $field['rules'] : [];
            }

            // Unpack until we get to the root type
            while ($type instanceof WrappingType) {
                if ($type instanceof ListOfType) {
                    // This lets us skip one level of validation rules
                    $fieldDepth--;
                    // Add this to the prefix to allow Laravel validation for arrays
                    $key .= '.*';
                }

                $type = $type->getWrappedType();
            }

            // Add explicitly set rules if they apply
            if (sizeof($fieldRules)) {
                $rules[$key] = $this->resolveRules($fieldRules, $resolutionArguments);
            }

            if ($type instanceof InputObjectType) {
                // Recursively call the parent method to get nested rules, passing in the new prefix
                $rules = array_merge($rules, $this->getRulesFromArgs($type->getFields(), $fieldDepth, $key, $resolutionArguments));
            }
        }

        return $rules;
    }

    /**
     * @param $rules
     * @param $arguments
     * @return mixed
     */
    protected function resolveRules($rules, $arguments)
    {
        // Rules can be defined as closures
        if (is_callable($rules)) {
            return call_user_func_array($rules, $arguments);
        }

        return $rules;
    }

    /**
     * Can be overwritten to define rules.
     *
     * @deprecated Will be removed in favour of defining rules together with the args.
     * @return array
     */
    protected function rules()
    {
        return [];
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
