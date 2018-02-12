<?php

use Folklore\GraphQL\Relay\NodeResponse;
use Illuminate\Support\Fluent;

/**
 * @coversDefaultClass \Folklore\GraphQL\Relay\NodeResponse
 */
class RelayNodeResponseTest extends RelayTestCase
{
    protected $thisresponse;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->response = new NodeResponse();
    }

    /**
     * Test setting the node and accessing attributes
     *
     * @test
     * @covers ::setNode
     */
    public function testSetNode()
    {
        $node = new Fluent();
        $node->test = 'test';
        $this->response->setNode($node);
        $this->assertEquals($this->response->test, $node->test);
        $this->assertEquals([
            'test' => 'test'
        ], $this->response->toArray());
    }

    /**
     * Test getting the original node
     *
     * @test
     * @covers ::setNode
     * @covers ::getOriginalNode
     */
    public function testGetOriginalNode()
    {
        $node = new Fluent();
        $node->test = 'test';
        $this->response->setNode($node);
        $this->assertEquals($node, $this->response->getOriginalNode());
    }

    /**
     * Test get and set type
     *
     * @test
     * @covers ::setType
     * @covers ::getType
     */
    public function testSetType()
    {
        $this->assertNull($this->response->getType());
        $this->response->setType('type');
        $this->assertEquals('type', $this->response->getType());
    }
}
