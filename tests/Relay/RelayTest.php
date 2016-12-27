<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\NodeIdField;

class RelayTest extends RelayTestCase
{
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
