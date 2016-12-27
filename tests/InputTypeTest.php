<?php

use Folklore\Support\Field;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\InputObjectType;

class InputTypeTest extends TestCase
{
    /**
     * Test to type
     *
     * @test
     */
    public function testToType()
    {
        $type = new \App\GraphQL\Type\ExampleInputType();
        $objectType = $type->toType();
        
        $this->assertInstanceOf(InputObjectType::class, $objectType);
    }
}
