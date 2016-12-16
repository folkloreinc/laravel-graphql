<?php

use Folklore\Support\Field;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\InterfaceType;

class InterfaceTypeTest extends TestCase
{
    /**
     * Test get attributes
     *
     * @test
     */
    public function testToArray()
    {
        $type = new \App\GraphQL\Type\ExampleInterfaceType();
        $array = $type->toArray();
        
        $this->assertArrayHasKey('resolveType', $array);
        $this->assertInstanceOf(Closure::class, $array['resolveType']);
    }
    
    /**
     * Test get attributes resolve type
     *
     * @test
     */
    public function testResolveType()
    {
        $type = $this->getMockBuilder(\App\GraphQL\Type\ExampleInterfaceType::class)
                    ->setMethods(['resolveType'])
                    ->getMock();

        $type->expects($this->once())
            ->method('resolveType');
        
        $array = $type->toArray();
        $array['resolveType'](null);
    }
       
    /**
     * Test to type
     *
     * @test
     */
    public function testToType()
    {
        $type = new \App\GraphQL\Type\ExampleInterfaceType();
        $interfaceType = $type->toType();
        
        $this->assertInstanceOf(InterfaceType::class, $interfaceType);
        
        $this->assertEquals($interfaceType->name, $type->name);
        
        $fields = $interfaceType->getFields();
        $this->assertArrayHasKey('name', $fields);
    }
}
