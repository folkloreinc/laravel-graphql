<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\NodeQuery;
use Folklore\GraphQL\Relay\NodeIdField;
use Folklore\GraphQL\Relay\NodeResponse;
use GraphQL\Type\Definition\ResolveInfo;

class RelayNodeQueryTest extends RelayTestCase
{
    /**
     * Test schema default
     *
     * @test
     */
    public function testHasIdArgs()
    {
        $query = new NodeQuery();
        $queryArray = $query->toArray();
        $this->assertArrayHasKey('id', $queryArray['args']);
    }
    
    /**
     * Test schema default
     *
     * @test
     */
    public function testTypeIsNode()
    {
        $query = new NodeQuery();
        $queryArray = $query->toArray();
        $this->assertEquals(GraphQL::type('Node'), $queryArray['type']);
    }
    
    /**
     * Test schema default
     *
     * @test
     */
    public function testResolveReturnNodeResponse()
    {
        $info = new ResolveInfo([]);
        $id = Relay::toGlobalId('ExampleNode', 1);
        
        $query = new NodeQuery();
        $response = $query->resolve(null, [
            'id' => $id
        ], null, $info);
        
        $this->assertInstanceOf(NodeResponse::class, $response);
    }
    
    /**
     * Test schema default
     *
     * @test
     * @expectedException \Folklore\GraphQL\Exception\TypeNotFound
     */
    public function testResolveThrowExceptionWhenTypeIsNotFound()
    {
        $info = new ResolveInfo([]);
        $id = Relay::toGlobalId('ExampleNodeNotFound', 1);
        
        $query = new NodeQuery();
        $response = $query->resolve(null, [
            'id' => $id
        ], null, $info);
        
        $this->assertNull($response);
    }
    
    /**
     * Test schema default
     *
     * @test
     * @expectedException \Folklore\GraphQL\Relay\Exception\NodeInvalid
     */
    public function testResolveThrowExceptionWhenTypeIsInvalid()
    {
        $info = new ResolveInfo([]);
        $id = Relay::toGlobalId('Example', 1);
        
        $query = new NodeQuery();
        $response = $query->resolve(null, [
            'id' => $id
        ], null, $info);
        
        $this->assertNull($response);
    }
}
