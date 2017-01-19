<?php

use Folklore\GraphQL\Relay\EdgesCollection;
use Illuminate\Support\Fluent;

/**
 * @coversDefaultClass \Folklore\GraphQL\Relay\EdgesCollection
 */
class RelayEdgesCollectionTest extends RelayTestCase
{
    protected $collection;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->collection = new EdgesCollection();
    }

    /**
     * Test set and get hasNextPage
     *
     * @test
     * @covers ::setHasNextPage
     * @covers ::getHasNextPage
     */
    public function testGetHasNextPage()
    {
        $this->assertEquals(false, $this->collection->getHasNextPage());
        $this->collection->setHasNextPage(true);
        $this->assertEquals(true, $this->collection->getHasNextPage());
    }

    /**
     * Test set and get hasPreviousPage
     *
     * @test
     * @covers ::setHasPreviousPage
     * @covers ::getHasPreviousPage
     */
    public function testGetHasPreviousPage()
    {
        $this->assertEquals(false, $this->collection->getHasPreviousPage());
        $this->collection->setHasPreviousPage(true);
        $this->assertEquals(true, $this->collection->getHasPreviousPage());
    }

    /**
     * Test set and get startCursor
     *
     * @test
     * @covers ::setStartCursor
     * @covers ::getStartCursor
     */
    public function testGetStartCursor()
    {
        $this->assertEquals(null, $this->collection->getStartCursor());
        $this->collection->setStartCursor(1);
        $this->assertEquals(1, $this->collection->getStartCursor());
    }

    /**
     * Test set and get endCursor
     *
     * @test
     * @covers ::setEndCursor
     * @covers ::getEndCursor
     */
    public function testGetEndCursor()
    {
        $this->assertEquals(null, $this->collection->getEndCursor());
        $this->collection->setEndCursor(1);
        $this->assertEquals(1, $this->collection->getEndCursor());
    }
}
