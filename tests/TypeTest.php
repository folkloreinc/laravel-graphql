<?php

namespace Folklore\GraphQL\Tests;

use GraphQL;
use Folklore\Support\Field;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Tests\Objects\ExampleType;

class TypeTest extends TestCase
{    
    /**
     * Test get attributes
     *
     * @test
     */
    public function testGetAttributes()
    {
        $type = $this->getMockBuilder(ExampleType::class)
                         ->setMethods(['getFields'])
                         ->getMock();

        $type->expects($this->once())
                 ->method('getFields');
                 
        $attributes = $type->getAttributes();
        $this->assertArrayHasKey('fields', $attributes);
        $attributes['fields']();
    } 
       
    /**
     * Test to array
     *
     * @test
     */
    public function testToArray()
    {
        $type = new ExampleType();
        $attributes = $type->getAttributes();
        $array = $type->toArray();
        $this->assertEquals($attributes, $array);
    }
    
    /**
     * Test getFields
     *
     * @test
     */
    public function testGetFields()
    {
        $type = new ExampleType();
        $fields = $type->getFields();
        
        $this->assertEquals($fields, [
            'test' => [
                'type' => Type::string(),
                'description' => 'A test field'
            ]
        ]);
    }
}
