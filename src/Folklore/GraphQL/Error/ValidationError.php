<?php namespace Folklore\GraphQL\Error;

use GraphQL\Error\Error;
use Illuminate\Validation\Validator;

class ValidationError extends Error
{
    /**
     * @var Validator
     */
    public $validator;

    /**
     * @param Validator $validator
     * @return $this
     */
    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;
        
        return $this;
    }

    /**
     * @return Validator
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * @return array|\Illuminate\Support\MessageBag
     */
    public function getValidatorMessages()
    {
        return $this->validator ? $this->validator->messages() : [];
    }
}
