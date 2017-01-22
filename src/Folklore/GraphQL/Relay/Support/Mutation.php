<?php

namespace Folklore\GraphQL\Relay\Support;

use Folklore\GraphQL\Support\Mutation as BaseMutation;
use Folklore\GraphQL\Relay\MutationResponse;

class Mutation extends BaseMutation
{
    protected $inputType;

    protected function inputType()
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

    protected function args()
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

    protected function getClientMutationId($root, $args)
    {
        return array_get($args, 'input.clientMutationId');
    }

    public function getResolver()
    {
        $resolver = parent::getResolver();

        return function () use ($resolver) {
            $args = func_get_args();
            $response = call_user_func_array($resolver, $args);
            $clientMutationId = call_user_func_array([$this, 'getClientMutationId'], $args);
            $response = $this->getMutationResponse($response, $clientMutationId);
            return $response;
        };
    }
}
