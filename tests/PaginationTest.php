<?php

use Illuminate\Validation\Validator;

class PaginationTest extends FieldTest
{
    /**
     * Test resolve.
     *
     * @test
     */
    public function testPaginationHasCursor()
    {
        $take = 2;
        $page = 1;

        // Act
        $result = GraphQL::query($this->queries['examplePagination'], [
            'take' => $take,
            'page' => $page,
        ]);

        // Assert
        $cursor = $result['data']['examplesPagination']['cursor'];

        $this->assertEquals($cursor['total'], count($this->data));
        $this->assertEquals($cursor['perPage'], $take);
        $this->assertEquals($cursor['currentPage'], $page);
        $this->assertEquals($cursor['hasPages'], count($this->data) > $take);
    }
}
