<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Error;
use Folklore\GraphQL\Error\ValidationError;
use Folklore\GraphQL\Events\TypeAdded;
use Folklore\GraphQL\Events\SchemaAdded;

class RelayNodeTypeTest extends RelayTestCase
{
    /**
     * Test schema default
     *
     * @test
     */
    public function testHasNodeInterface()
    {
        $type = new \App\GraphQL\Relay\Type\ExampleNodeType();
        $typeArray = $type->toArray();
        
        $this->assertEquals($typeArray['interfaces'][0], GraphQL::type('Node'));
    }
    
    /**
     * Test schema default
     *
     * @test
     */
    public function testHasIdField()
    {
        $type = new \App\GraphQL\Relay\Type\ExampleNodeType();
        $fields = $type->getFields();
        $this->assertArrayHasKey('id', $fields);
        $this->assertEquals($fields['id']['type'], Type::nonNull(Type::id()));
    }
}
