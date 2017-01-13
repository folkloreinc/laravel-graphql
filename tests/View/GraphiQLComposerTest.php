<?php

use Folklore\GraphQL\View\GraphiQLComposer;

/**
 * @coversDefaultClass \Folklore\GraphQL\View\GraphiQLComposer
 */
class GraphiQLComposerTest extends TestCase
{
    protected $composer;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->composer = app(GraphiQLComposer::class);
    }

    /**
     * Test the compose method
     *
     * @test
     * @covers ::compose
     */
    public function testCompose()
    {
        $view = view('graphql::graphiql');

        $this->composer->compose($view);
        $data = $view->getData();
        $this->assertArrayHasKey('graphqlPath', $data);
        $this->assertEquals(route('graphql.query'), $data['graphqlPath']);
    }
}
