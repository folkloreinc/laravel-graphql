<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\ConnectionEdgeType;
use Folklore\GraphQL\Relay\EdgeObjectType;

class RelayConnectionEdgeTypeTest extends RelayTestCase
{
    /**
     * Test edge fields
     *
     * @test
     */
    public function testFields()
    {
        $edgeObjectType = new ConnectionEdgeType([
            'name' => 'Test'
        ]);
        $fields = $edgeObjectType->getFields();
        
        $this->assertEquals([
            'cursor' => [
                'type' => Type::nonNull(Type::id())
            ],
            'node' => [
                'type' => GraphQL::type('Node')
            ]
        ], $fields);
    }
    
    /**
     * Test toType method
     *
     * @test
     */
    public function testToType()
    {
        $edgeObjectType = new ConnectionEdgeType([
            'name' => 'Test'
        ]);
        $type = $edgeObjectType->toType();
        
        $this->assertInstanceOf(EdgeObjectType::class, $type);
    }
}
