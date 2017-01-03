<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\NodeInterface;
use Folklore\GraphQL\Relay\NodeResponse;
use Folklore\GraphQL\Support\InterfaceType;

class RelayNodeInterfaceTest extends RelayTestCase
{
    /**
     * Test is interface type
     *
     * @test
     */
    public function testIsInterfaceType()
    {
        $interface = new NodeInterface();
        $this->assertInstanceOf(InterfaceType::class, $interface);
    }
    
    /**
     * Test has id field
     *
     * @test
     */
    public function testHasIdField()
    {
        $interface = new NodeInterface();
        $fields = $interface->getFields();
        $this->assertArrayHasKey('id', $fields);
        $this->assertEquals($fields['id'], [
            'type' => Type::nonNull(Type::id())
        ]);
    }
    
    /**
     * Test has id field
     *
     * @test
     */
    public function testResolveTypeFromNodeResponse()
    {
        $type = GraphQL::type('Example');
        $response = new NodeResponse();
        $response->setType($type);
        
        $interface = new NodeInterface();
        $this->assertEquals($interface['resolveType']($response), $type);
    }
    
    /**
     * Test has id field
     *
     * @test
     * @expectedException \Folklore\GraphQL\Relay\Exception\NodeRootInvalid
     */
    public function testResolveTypeFromOther()
    {
        $interface = new NodeInterface();
        $interface['resolveType']('root');
    }
}
