<?php

namespace Folklore\GraphQL\Relay\Support\Traits;

use GraphQL;
use GraphQL\Type\Definition\Type;

trait HasClientMutationIdField
{
    /**
     * @return mixed
     */
    public function getFields()
    {
        $fields = parent::getFields();

        $fields['clientMutationId'] = [
            'type' => Type::nonNull(Type::string()),
        ];

        return $fields;
    }
}
