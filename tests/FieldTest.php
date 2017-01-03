<?php

use Folklore\Support\Field;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;

class FieldTest extends TestCase
{
    protected function getFieldClass()
    {
        return \App\GraphQL\Field\ExampleField::class;
    }

    /**
     * Test get attributes
     *
     * @test
     */
    public function testGetAttributes()
    {
        $class = $this->getFieldClass();
        $field = new $class();
        $attributes = $field->getAttributes();
        
        $this->assertArrayHasKey('name', $attributes);
        $this->assertInternalType('array', $attributes);
    }
       
    /**
     * Test to array
     *
     * @test
     */
    public function testToArray()
    {
        $class = $this->getFieldClass();
        $field = new $class();
        $array = $field->toArray();
        
        $this->assertInternalType('array', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('args', $array);
        $this->assertArrayHasKey('resolve', $array);
        $this->assertInstanceOf(Closure::class, $array['resolve']);
        $this->assertInstanceOf(get_class($field->getType()), $array['type']);
    }
    
    /**
     * Test resolve closure
     *
     * @test
     */
    public function testResolve()
    {
        $class = $this->getFieldClass();
        $field = $this->getMockBuilder($class)
                    ->setMethods(['resolve'])
                    ->getMock();

        $field->expects($this->once())
            ->method('resolve');
        
        $attributes = $field->toArray();
        $attributes['resolve'](null, [], [], null);
    }
}
