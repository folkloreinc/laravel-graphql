<?php

use Folklore\GraphQL\View\GraphiQLComposer;

class GraphiQLTest extends TestCase
{
    /**
     * Test endpoint
     *
     * @test
     */
    public function testViewExists()
    {
        $this->assertTrue(app('view')->exists('graphql::graphiql'));
    }
    
    /**
     * Test endpoint
     *
     * @test
     */
    public function testEndpoint()
    {
        $queryPath = route('graphql.query');
        
        $response = $this->call('GET', route('graphql.graphiql'));
        $this->assertResponseOk();
        $this->assertViewHas('graphqlPath', $queryPath);
        
        $content = $response->getContent();
        $this->assertContains($queryPath, $content);
    }
}
