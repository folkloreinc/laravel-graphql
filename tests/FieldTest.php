<?php

use Folklore\GraphQL\Support\Field;

class FieldTest extends TestCase
{
    /**
     * @return string
     */
    protected function getFieldClass()
    {
        return ExampleField::class;
    }

    /**
     * @return Field
     */
    protected function getFieldInstance()
    {
        $class = $this->getFieldClass();
        return new $class();
    }

    /**
     * Test get attributes
     *
     * @test
     */
    public function testGetAttributes()
    {
        $field = $this->getFieldInstance();
        $attributes = $field->getAttributes();
        
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayHasKey('type', $attributes);
        $this->assertArrayHasKey('args', $attributes);
        $this->assertArrayHasKey('resolve', $attributes);
        $this->assertInternalType('array', $attributes['args']);
        $this->assertInstanceOf(Closure::class, $attributes['resolve']);
        $this->assertInstanceOf(get_class($field->type()), $attributes['type']);
    }
    
    /**
     * Test the calling of a custom resolve function.
     *
     * @test
     */
    public function testResolveFunctionIsCalled()
    {
        $class = $this->getFieldClass();
        $field = $this->getMockBuilder($class)
                    ->setMethods(['resolve'])
                    ->getMock();

        $field->expects($this->once())
            ->method('resolve');
        
        $attributes = $field->getAttributes();
        $attributes['resolve'](null, [], [], new \GraphQL\Type\Definition\ResolveInfo([]));
    }
       
    /**
     * Test to array
     *
     * @test
     */
    public function testToArray()
    {
        $field = $this->getFieldInstance();
        $array = $field->toArray();
        
        $this->assertInternalType('array', $array);
        
        $attributes = $field->getAttributes();
        $this->assertEquals($attributes, $array);
    }
}
