<?php

namespace Folklore\GraphQL\Support\Traits;

use Validator;
use Folklore\GraphQL\Error\ValidationError;
use Closure;

trait ShouldValidate
{
    protected $rules = [];
    
    protected function rules()
    {
        return [];
    }
    
    protected function setRules($rules)
    {
        $this->rules = $rules;
    }
    
    public function getRules()
    {
        return $this->rules;
    }
    
    public function getRulesForValidator()
    {
        $arguments = func_get_args();
        
        $rules = call_user_func_array([$this, 'rules'], $arguments);
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
        
        return array_merge($this->rules, $rules, $argsRules);
    }
    
    protected function getValidator($args, $rules)
    {
        return Validator::make($args, $rules);
    }
    
    protected function getResolver()
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
