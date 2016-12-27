<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;

class RelayPayloadTypeTest extends RelayTestCase
{
    public function testIsType()
    {
        $payloadType = new \App\GraphQL\Relay\Type\UpdateNamePayloadType();
        
        $this->assertInstanceOf(\Folklore\GraphQL\Support\Type::class, $payloadType);
    }
    
    public function testHasClientMutationIdField()
    {
        $payloadType = new \App\GraphQL\Relay\Type\UpdateNamePayloadType();
        $fields = $payloadType->getFields();
        
        $this->assertArrayHasKey('clientMutationId', $fields);
    }
}
