<?php

use Folklore\GraphQL\Support\InputType;
use GraphQL\Type\Definition\InputObjectType;

/**
 * @coversDefaultClass \Folklore\GraphQL\Support\InputType
 */
class InputTypeTest extends TestCase
{
    protected $type;

    public function setUp()
    {
        parent::setUp();

        $this->type = app(InputType::class);
    }

    /**
     * Test to type
     *
     * @test
     * @covers ::toType
     */
    public function testToType()
    {
        $this->type->name = 'ExampleType';
        $objectType = $this->type->toType();
        $this->assertInstanceOf(InputObjectType::class, $objectType);
    }
}
