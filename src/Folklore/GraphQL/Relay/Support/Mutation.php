<?php

namespace Folklore\GraphQL\Relay\Support;

use Folklore\GraphQL\Support\Mutation as BaseMutation;
use Folklore\GraphQL\Relay\MutationResponse;

class Mutation extends BaseMutation
{
    protected $inputType;
    
    public function inputType()
    {
        return null;
    }
    
    public function getInputType()
    {
        $inputType = $this->inputType();
        return $inputType ? $inputType:$this->inputType;
    }
    
    public function setInputType($inputType)
    {
        $this->inputType = $inputType;
    }
    
    public function args()
    {
        return [
            'input' => [
                'name' => 'input',
                'type' => $this->getInputType()
            ]
        ];
    }
    
    protected function getMutationResponse($response, $clientMutationId)
    {
        $mutationResponse = new MutationResponse();
        $mutationResponse->setNode($response);
        $mutationResponse->setClientMutationId($clientMutationId);
        
        return $mutationResponse;
    }
    
    protected function getResolver()
    {
        $resolver = parent::getResolver();
        
        return function () use ($resolver) {
            $args = func_get_args();
            $response = call_user_func_array($resolver, $args);
            $clientMutationId = array_get($args, '1.input.clientMutationId');
            return $this->getMutationResponse($response, $clientMutationId);
        };
    }
}
