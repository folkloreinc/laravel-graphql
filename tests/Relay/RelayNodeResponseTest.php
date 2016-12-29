<?php

use Folklore\GraphQL\Relay\NodeResponse;
use Illuminate\Support\Fluent;

class RelayNodeResponseTest extends RelayTestCase
{
    protected $response;
    
    /**
     * Test schema default
     *
     * @test
     */
    public function testSetNode()
    {
        $response = new NodeResponse();
        $node = new Fluent();
        $node->test = 'test';
        $response->setNode($node);
        $this->assertEquals($response->test, $node->test);
    }
    
    /**
     * Test schema default
     *
     * @test
     */
    public function testGetOriginalNode()
    {
        $response = new NodeResponse();
        $node = new Fluent();
        $node->test = 'test';
        $response->setNode($node);
        $this->assertEquals($node, $response->getOriginalNode());
    }
    
    /**
     * Test set type
     *
     * @test
     */
    public function testSetType()
    {
        $response = new NodeResponse();
        $response->setType('type');
        $this->assertEquals('type', $response->getType());
    }
}
