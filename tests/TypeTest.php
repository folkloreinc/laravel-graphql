<?php

use Folklore\Support\Field;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;

class TypeTest extends TestCase
{
    /**
     * Test getFields
     *
     * @test
     */
    public function testGetFields()
    {
        $type = new \App\GraphQL\Type\ExampleType();
        $fields = $type->getFields();
        
        $this->assertInternalType('array', $fields);
        $this->assertArrayHasKey('name', $fields);
        $this->assertEquals($fields['name'], [
            'type' => Type::string(),
            'description' => 'The name field'
        ]);
        $this->assertInternalType('string', $fields['name_validation']);
        $this->assertTrue(class_exists($fields['name_validation']));
        $this->assertInstanceOf(\App\GraphQL\Field\ExampleValidationField::class, app($fields['name_validation']));
    }
    
    /**
     * Test getFields
     *
     * @test
     */
    public function testGetFieldsForObjectType()
    {
        $type = new \App\GraphQL\Type\ExampleType();
        $fields = $type->getFieldsForObjectType();
        
        $field = new \App\GraphQL\Field\ExampleValidationField();
        $field->name = 'name_validation';
        
        $this->assertInternalType('array', $fields);
        $this->assertArrayHasKey('name', $fields);
        $this->assertEquals($fields['name'], [
            'type' => Type::string(),
            'description' => 'The name field'
        ]);
        $this->assertInternalType('array', $fields['name_validation']);
        $this->assertEquals($fields['name_validation'], $field->toArray());
    }
       
    /**
     * Test get attributes
     *
     * @test
     */
    public function testGetAttributes()
    {
        $type = new \App\GraphQL\Type\ExampleType();
        $attributes = $type->getAttributes();
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
        $type = new \App\GraphQL\Type\ExampleType();
        $array = $type->toArray();
        
        $this->assertArrayHasKey('name', $array);
        $this->assertInternalType('array', $array);
        $this->assertArrayHasKey('fields', $array);
        $this->assertInstanceOf(Closure::class, $array['fields']);
        $this->assertInternalType('array', $array['fields']());
    }
    
    /**
     * Test fields closure
     *
     * @test
     */
    public function testToArrayFieldsClosure()
    {
        $type = $this->getMockBuilder(\App\GraphQL\Type\ExampleType::class)
                    ->setMethods(['getFieldsForObjectType'])
                    ->getMock();

        $type->expects($this->once())
            ->method('getFieldsForObjectType');
        
        $attributes = $type->toArray();
        $attributes['fields']();
    }
       
    /**
     * Test to type
     *
     * @test
     */
    public function testToType()
    {
        $type = new \App\GraphQL\Type\ExampleType();
        $objectType = $type->toType();
        
        $this->assertInstanceOf(ObjectType::class, $objectType);
        $this->assertEquals($objectType->name, $type->name);
        $this->assertEquals(array_keys($objectType->getFields()), array_keys($type->getFields()));
    }
       
    /**
     * Test that resolve call the associated resolve method
     *
     * @test
     */
    public function testResolveMethod()
    {
        $type = $this->getMockBuilder(\App\GraphQL\Type\ExampleType::class)
                    ->setMethods(['resolveNameMethodField'])
                    ->getMock();

        $type->expects($this->once())
            ->method('resolveNameMethodField');
            
        $fields = $type->getFieldsForObjectType();
        $fields['name_method']['resolve']();
    }
}
