<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\NodeIdField;
use Folklore\GraphQL\Relay\Support\ConnectionField;
use Folklore\GraphQL\Relay\Support\ConnectionType;

class RelayTest extends RelayTestCase
{
    /**
     * Test connectionField method
     *
     * @test
     */
    public function testConnectionField()
    {
        $field = Relay::connectionField([
            'name' => 'TestConnectionField'
        ]);
        $this->assertInstanceOf(ConnectionField::class, $field);
    }
    
    /**
     * Test connectionFieldFromEdgeType method
     *
     * @test
     */
    public function testConnectionFieldFromEdgeType()
    {
        $edgeType = GraphQL::type('ExampleNode');
        $field = Relay::connectionFieldFromEdgeType($edgeType);
        $this->assertInstanceOf(ConnectionField::class, $field);
        $type = $field->getType();
        $this->assertInstanceOf(ObjectType::class, $type);
        
        $typeName = $type->config['name'];
        $types = GraphQL::getTypes();
        $this->assertArrayHasKey($typeName, $types);
        $this->assertInstanceOf(ConnectionType::class, $types[$typeName]);
        $this->assertEquals($edgeType, $types[$typeName]->getEdgeType());
    }
    
    /**
     * Test toGlobalId method
     *
     * @test
     */
    public function testToGlobalId()
    {
        $globalId = Relay::toGlobalId('Type', 1);
        $this->assertEquals(base64_encode(implode(':', ['Type',1])), $globalId);
    }
    
    /**
     * Test fromGlobalId method
     *
     * @test
     */
    public function testFromGlobalId()
    {
        $globalId = base64_encode(implode(':', ['Type',1]));
        $parts = Relay::fromGlobalId($globalId);
        $this->assertEquals($parts[0], 'Type');
        $this->assertEquals($parts[1], 1);
    }
    
    /**
     * Test getIdFromGlobalId method
     *
     * @test
     */
    public function testGetIdFromGlobalId()
    {
        $globalId = base64_encode(implode(':', ['Type',1]));
        $id = Relay::getIdFromGlobalId($globalId);
        $this->assertEquals($id, 1);
    }
    
    /**
     * Test getTypeFromGlobalId method
     *
     * @test
     */
    public function testGetTypeFromGlobalId()
    {
        $globalId = base64_encode(implode(':', ['Type',1]));
        $type = Relay::getTypeFromGlobalId($globalId);
        $this->assertEquals($type, 'Type');
    }
}
