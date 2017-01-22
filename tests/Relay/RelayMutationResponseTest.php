<?php

use Folklore\GraphQL\Relay\MutationResponse;
use Illuminate\Support\Fluent;

/**
 * @coversDefaultClass \Folklore\GraphQL\Relay\MutationResponse
 */
class RelayMutationResponseTest extends RelayTestCase
{
    protected $response;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->response = new MutationResponse();
    }

    /**
     * Test setting the node and getting the attributes
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
     * Test set and get clientMutationId
     *
     * @test
     * @covers ::setClientMutationId
     * @covers ::getClientMutationId
     */
    public function testSetType()
    {
        $this->response->setClientMutationId('id');
        $this->assertEquals('id', $this->response->getClientMutationId());
    }
}
