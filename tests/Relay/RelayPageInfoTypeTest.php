<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\PageInfoType;
use Folklore\GraphQL\Support\Type as BaseType;

/**
 * @coversDefaultClass \Folklore\GraphQL\Relay\PageInfoType
 */
class RelayPageInfoTypeTest extends RelayTestCase
{
    protected $type;

    public function setUp()
    {
        parent::setUp();

        $this->type = new PageInfoType();
    }

    /**
     * Test that PageInfoType is a BaseType
     *
     * @test
     * @covers ::__construct
     */
    public function testPageInfoIsType()
    {
        $this->assertInstanceOf(BaseType::class, $this->type);
    }

    /**
     * Test that PageInfoType has the correct fields
     *
     * @test
     * @covers ::fields
     */
    public function testHasFields()
    {
        $fields = $this->type->getFields();
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
