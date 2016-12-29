<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\NodeIdField;
use Folklore\GraphQL\Support\Field;

class RelayNodeIdFieldTest extends RelayTestCase
{
    /**
     * Test is field
     *
     * @test
     */
    public function testIsField()
    {
        $field = new NodeIdField();
        $this->assertInstanceOf(Field::class, $field);
    }
    
    /**
     * Test type is okay
     *
     * @test
     */
    public function testTypeIsId()
    {
        $field = new NodeIdField();
        $this->assertEquals(Type::nonNull(Type::id()), $field->type());
    }
    
    /**
     * Test is field type
     *
     * @test
     */
    public function testResolve()
    {
        $field = new NodeIdField();
        $field->setIdResolver(function () {
            return 1;
        });
        $field->setIdType('Type');
        $globalId = Relay::toGlobalId('Type', 1);
        $this->assertEquals($field->resolve(), $globalId);
    }
}
