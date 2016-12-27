<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;

class RelayInputTypeTest extends RelayTestCase
{
    public function testIsType()
    {
        $payloadType = new \App\GraphQL\Relay\Type\UpdateNameInputType();
        
        $this->assertInstanceOf(\Folklore\GraphQL\Support\InputType::class, $payloadType);
    }
    
    public function testHasClientMutationIdField()
    {
        $payloadType = new \App\GraphQL\Relay\Type\UpdateNameInputType();
        $fields = $payloadType->getFields();
        
        $this->assertArrayHasKey('clientMutationId', $fields);
    }
}
