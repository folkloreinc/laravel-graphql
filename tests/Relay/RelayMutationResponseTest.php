<?php

use Folklore\GraphQL\Relay\MutationResponse;
use Illuminate\Support\Fluent;

class RelayMutationResponseTest extends RelayTestCase
{
    protected $response;
    
    /**
     * Test schema default
     *
     * @test
     */
    public function testSetNode()
    {
        $response = new MutationResponse();
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
        $response = new MutationResponse();
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
        $response = new MutationResponse();
        $response->setClientMutationId('id');
        $this->assertEquals('id', $response->getClientMutationId());
    }
}
