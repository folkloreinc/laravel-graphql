<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\PageInfoType;
use Folklore\GraphQL\Support\Type as BaseType;

class RelayPageInfoTypeTest extends RelayTestCase
{
    /**
     * Test that PageInfoType is a BaseType
     *
     * @test
     */
    public function testPageInfoIsType()
    {
        $type = new PageInfoType();
        $this->assertInstanceOf(BaseType::class, $type);
    }
    
    /**
     * Test that PageInfoType has the correct fields
     *
     * @test
     */
    public function testHasFields()
    {
        $type = new PageInfoType();
        $fields = $type->getFields();
        $schema = [
            'hasNextPage' => [
                'type' => Type::nonNull(Type::boolean())
            ],
            'hasPreviousPage' => [
                'type' => Type::nonNull(Type::boolean())
            ],
            'startCursor' => [
                'type' => Type::string()
            ],
            'endCursor' => [
                'type' => Type::string()
            ]
        ];
        $this->assertEquals($fields, $schema);
    }
}
