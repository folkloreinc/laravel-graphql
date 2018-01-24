<?php

namespace Folklore\GraphQL\Relay\Support\Traits;

use Folklore\GraphQL\Relay\NodeIdField;

trait TypeIsNode
{
    /**
     * @return mixed
     */
    public function getFieldsForObjectType()
    {
        $currentFields = parent::getFieldsForObjectType();

        $idResolver  = $this->getIdResolverFromFields($currentFields);
        $nodeIdField = $this->getNodeIdField();
        $nodeIdField->setIdResolver($idResolver);
        $currentFields['id'] = $nodeIdField->toArray();

        return $currentFields;
    }

    /**
     * @return mixed
     */
    protected function getNodeIdField()
    {
        $nodeIdField = new NodeIdField();
        $nodeIdField->setIdType($this->name);
        return $nodeIdField;
    }

    /**
     * @param $fields
     * @return mixed
     */
    protected function getIdResolverFromFields($fields)
    {
        $idResolver       = null;
        $originalResolver = array_get($fields, 'id.resolve');
        if ($originalResolver) {
            $idResolver = function () use ($originalResolver) {
                $id = call_user_func_array($originalResolver, func_get_args());
                return $id;
            };
        } else {
            $idResolver = function ($root) {
                return array_get($root, 'id');
            };
        }

        return $idResolver;
    }

    protected function relayInterfaces()
    {
        return [
            app('graphql')->type('Node'),
        ];
    }

    public function getInterfaces()
    {
        $interfaces      = parent::getInterfaces();
        $relayInterfaces = $this->relayInterfaces();
        return array_merge($interfaces, $relayInterfaces);
    }
}
