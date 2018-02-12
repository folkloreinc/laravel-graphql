<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\NodeQuery;
use Folklore\GraphQL\Relay\NodeIdField;
use Folklore\GraphQL\Relay\NodeResponse;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @coversDefaultClass \Folklore\GraphQL\Relay\NodeQuery
 */
class RelayNodeQueryTest extends RelayTestCase
{
    protected $query;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->query = new NodeQuery();
    }

    /**
     * Test that there is an id args
     *
     * @test
     * @covers ::args
     */
    public function testHasIdArgs()
    {
        $queryArray = $this->query->toArray();
        $this->assertArrayHasKey('id', $queryArray['args']);
    }

    /**
     * Test that the type is Node
     *
     * @test
     * @covers ::type
     */
    public function testTypeIsNode()
    {
        $queryArray = $this->query->toArray();
        $this->assertEquals(GraphQL::type('Node'), $queryArray['type']);
    }

    /**
     * Test that resolve return a NodeResponse
     *
     * @test
     * @covers ::resolve
     */
    public function testResolveReturnNodeResponse()
    {
        $info = new ResolveInfo([]);
        $id = Relay::toGlobalId('ExampleNode', 1);

        $response = $this->query->resolve(null, [
            'id' => $id
        ], null, $info);

        $this->assertInstanceOf(NodeResponse::class, $response);
    }

    /**
     * Test that resolve throw an exception when the type is not found
     *
     * @test
     * @covers ::resolve
     * @expectedException \Folklore\GraphQL\Exception\TypeNotFound
     */
    public function testResolveThrowExceptionWhenTypeIsNotFound()
    {
        $info = new ResolveInfo([]);
        $id = Relay::toGlobalId('ExampleNodeNotFound', 1);

        $response = $this->query->resolve(null, [
            'id' => $id
        ], null, $info);

        $this->assertNull($response);
    }

    /**
     * Test that resolve throw an exception when the type is not a node
     *
     * @test
     * @covers ::resolve
     * @expectedException \Folklore\GraphQL\Relay\Exception\NodeInvalid
     */
    public function testResolveThrowExceptionWhenTypeIsInvalid()
    {
        $info = new ResolveInfo([]);
        $id = Relay::toGlobalId('Example', 1);

        $response = $this->query->resolve(null, [
            'id' => $id
        ], null, $info);

        $this->assertNull($response);
    }
}
