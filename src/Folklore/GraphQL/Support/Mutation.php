<?php

namespace Folklore\GraphQL\Support;

use Validator;
use Folklore\GraphQL\Error\ValidationError;

class Mutation extends Field {
    
    protected function rules()
    {
        return [];
    }
    
    public function getRules()
    {
        $rules = $this->rules();
        $argsRules = [];
        foreach($this->args() as $name => $arg)
        {
            if(isset($arg['rules']))
            {
                $argsRules[$name] = $arg['rules'];
            }
        }
        return array_merge($argsRules, $rules);
    }
    
    protected function getResolver()
    {
        if(!method_exists($this, 'resolve'))
        {
            return null;
        }
        
        $rules = $this->getRules();
        $resolver = array($this, 'resolve');
        return function() use ($resolver, $rules)
        {
            $args = func_get_args();
            if(sizeof($rules))
            {
                $validator = Validator::make($args[1], $rules);
                if($validator->fails()) 
                {
                    throw with(new ValidationError('validation'))->setValidator($validator);
                }
            }
            return call_user_func_array($resolver, $args);
        };
    }
    
}
