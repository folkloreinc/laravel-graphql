<?php

namespace Folklore\GraphQL\Support\Traits;

use Folklore\GraphQL\Error\ValidationError;
use Closure;

trait ShouldValidate
{
    public function setRules($rules)
    {
        $this->attributes['rules'] = $rules;
        return $this;
    }

    public function getRules()
    {
        return array_get($this->attributes, 'rules');
    }

    protected function getRulesForValidator()
    {
        $arguments = func_get_args();

        $rules = array_get($this->attributes, 'rules');
        $methodRules = method_exists($this, 'rules') ?
            call_user_func_array([$this, 'rules'], $arguments):[];
        $argsRules = [];
        $args = $this->getArgs();
        foreach ($args as $name => $arg) {
            if (isset($arg['rules'])) {
                if ($arg['rules'] instanceof Closure) {
                    $argsRules[$name] = call_user_func_array($arg['rules'], $arguments);
                } else {
                    $argsRules[$name] = $arg['rules'];
                }
            }
        }

        return array_merge($rules ? $rules:$methodRules, $argsRules);
    }

    protected function getValidator($args, $rules)
    {
        return app('validator')->make($args, $rules);
    }

    public function getResolver()
    {
        $resolver = parent::getResolver();
        if (!$resolver) {
            return null;
        }

        return function () use ($resolver) {
            $arguments = func_get_args();

            $rules = call_user_func_array([$this, 'getRulesForValidator'], $arguments);
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
