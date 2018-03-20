<?php

use Illuminate\Validation\Validator;

class PaginationTest extends FieldTest
{
    /**
     * Test the pagination type
     *
     * @test
     */
    public function testPagination()
    {
        $take = 2;
        $page = 1;

        // Act
        $result = GraphQL::query($this->queries['examplePagination'], [
            'take' => $take,
            'page' => $page,
        ]);

        // Assert
        $items = $result['data']['examplesPagination']['items'];
        $cursor = $result['data']['examplesPagination']['cursor'];

        $this->assertEquals($cursor['total'], count($this->data));
        $this->assertEquals($cursor['perPage'], $take);
        $this->assertEquals($cursor['currentPage'], $page);
        $this->assertEquals($cursor['hasPages'], count($this->data) > $take);

        $this->assertEquals(count($items), $take);
        $this->assertEquals($items[0]['test'], $this->data[0]['test']);
        $this->assertEquals($items[1]['test'], $this->data[1]['test']);
    }
}
