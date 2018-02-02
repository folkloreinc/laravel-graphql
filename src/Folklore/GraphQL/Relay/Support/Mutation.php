<?php

namespace Folklore\GraphQL\Relay\Support;

use Folklore\GraphQL\Relay\MutationResponse;
use Folklore\GraphQL\Support\Mutation as BaseMutation;

class Mutation extends BaseMutation
{
    /**
     * @var mixed
     */
    protected $inputType;

    protected function inputType()
    {
        return null;
    }

    /**
     * @return mixed
     */
    public function getInputType()
    {
        $inputType = $this->inputType();
        return $inputType ? $inputType : $this->inputType;
    }

    /**
     * @param $inputType
     */
    public function setInputType($inputType)
    {
        $this->inputType = $inputType;
    }

    public function args()
    {
        return [
            'input' => [
                'name' => 'input',
                'type' => $this->getInputType(),
            ],
        ];
    }

    /**
     * @param $response
     * @param $clientMutationId
     * @return mixed
     */
    protected function getMutationResponse($response, $clientMutationId)
    {
        $mutationResponse = new MutationResponse();
        $mutationResponse->setNode($response);
        $mutationResponse->setClientMutationId($clientMutationId);

        return $mutationResponse;
    }

    /**
     * @param $root
     * @param $args
     */
    protected function resolveClientMutationId($root, $args)
    {
        return array_get($args, 'input.clientMutationId');
    }

    /**
     * @return mixed
     */
    public function getResolver()
    {
        $resolver = parent::getResolver();

        return function () use ($resolver) {
            $args             = func_get_args();
            $response         = call_user_func_array($resolver, $args);
            $clientMutationId = call_user_func_array([$this, 'resolveClientMutationId'], $args);
            $response         = $this->getMutationResponse($response, $clientMutationId);
            return $response;
        };
    }
}
