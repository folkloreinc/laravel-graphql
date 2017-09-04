<?php

namespace Folklore\GraphQL\Relay\Support\Traits;

use GraphQL;
use Folklore\GraphQL\Relay\NodeIdField;
use GraphQL\Type\Definition\Type;

trait HasClientMutationIdField
{
    public function getFields()
    {
        $fields = parent::getFields();
        
        $fields['clientMutationId'] = [
            'type' => Type::nonNull(Type::string())
        ];
        
        return $fields;
    }
}
