<?php

use Folklore\GraphQL\View\GraphiQLComposer;

class GraphiQLTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('graphql.routes', [
            'query' => 'query/{graphql_schema?}',
            'mutation' => 'mutation/{graphql_schema?}'
        ]);
    }

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
        $this->assertEquals(200, $response->status());
        $this->assertEquals($queryPath, $response->original->graphqlPath);
        $content = $response->getContent();
        $this->assertContains($queryPath, $content);
    }
}
