<?php

use Folklore\GraphQL\Support\Type;
use GraphQL\Type\Definition\ObjectType;

/**
 * @coversDefaultClass \Folklore\GraphQL\Support\Type
 */
class TypeTest extends TestCase
{
    protected $type;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->type = app(Type::class);
    }

    /**
     * Test that it can set and get interfaces
     *
     * @test
     * @covers ::interfaces
     * @covers ::setInterfaces
     * @covers ::getInterfaces
     */
    public function testGetInterfaces()
    {
        $baseInterfaces = [
            GraphQL::type('ExampleInterface')
        ];
        $this->type->setInterfaces($baseInterfaces);
        $interfaces = $this->type->getInterfaces();
        $this->assertEquals($baseInterfaces, $interfaces);

        $type = new ExampleType();
        $interfaces = $type->getInterfaces();
        $this->assertEquals($baseInterfaces, $interfaces);
    }

    /**
     * Test that it can set and get fields
     *
     * @test
     * @covers ::fields
     * @covers ::setFields
     * @covers ::getFields
     */
    public function testGetFields()
    {
        $field = new \App\GraphQL\Field\ExampleField();
        $baseFields = [
            'test' => [
                'type' => GraphQL::type('Example')
            ],
            'test_no_resolve' => [
                'type' => GraphQL::type('Example')
            ],
            'test_string' => \App\GraphQL\Field\ExampleField::class,
            'test_class' => $field
        ];

        $this->type->setFields($baseFields);
        $fields = $this->type->getFields();
        $this->assertEquals($baseFields, $fields);

        $type = new ExampleType();
        $fields = $type->getFields();
        $this->assertEquals($baseFields, $fields);
    }

    /**
     * Test that it can set and get fields
     *
     * @test
     * @covers ::getFieldsForObjectType
     * @covers ::getFieldResolver
     */
    public function testGetFieldsForObjectType()
    {
        $resolveMock = $this->getMockBuilder(ExampleFieldResolver::class)
            ->setMethods(['resolve'])
            ->getMock();
        $resolveMock->expects($this->once())
            ->method('resolve')
            ->willReturn('resolveReturn');

        // Configure the stub.
        $field = new \App\GraphQL\Field\ExampleField();
        $baseFields = [
            'test' => [
                'type' => GraphQL::type('Example'),
                'resolve' => [$resolveMock, 'resolve']
            ],
            'test_no_resolve' => [
                'type' => GraphQL::type('Example')
            ],
            'test_string' => \App\GraphQL\Field\ExampleField::class,
            'test_class' => $field
        ];

        $this->type->setFields($baseFields);
        $fields = $this->type->getFields();
        $this->assertEquals($baseFields, $fields);

        $fieldsForObjectType = $this->type->getFieldsForObjectType();
        $fieldsNames = array_keys($baseFields);
        foreach ($fieldsNames as $name) {
            $this->assertArrayHasKey($name, $fieldsForObjectType);
        }

        $this->assertEquals($fieldsForObjectType['test'], $baseFields['test']);
        $fieldArray = $field->toArray();
        $fieldArray['name'] = 'test_string';
        $this->assertEquals($fieldsForObjectType['test_string'], $fieldArray);
        $fieldArray['name'] = 'test_class';
        $this->assertEquals($fieldsForObjectType['test_class'], $fieldArray);

        //Test resolve
        foreach ($fieldsForObjectType as $key => $field) {
            if ($key !== 'test_no_resolve') {
                $this->assertArrayHasKey('resolve', $field);
            }
        }
        $this->assertEquals('resolveReturn', $fieldsForObjectType['test']['resolve']());

        $type = new ExampleType();
        $fieldsForObjectType = $type->getFieldsForObjectType();
        $this->assertEquals('resolveReturn', $fieldsForObjectType['test']['resolve']());
        $this->assertArrayNotHasKey('resolve', $fieldsForObjectType['test_no_resolve']);
    }

    /**
     * Test the toArray method
     *
     * @test
     * @covers ::attributes
     * @covers ::getAttributes
     * @covers ::__isset
     * @covers ::__get
     */
    public function testGetAttributes()
    {
        $baseAttributes = [
            'name' => 'test'
        ];
        $this->type->name = $baseAttributes['name'];
        $attributes = $this->type->getAttributes();
        $this->assertEquals($baseAttributes, $attributes);
        $this->assertTrue(isset($baseAttributes['name']));
        $this->assertEquals($baseAttributes['name'], $this->type->name);

        $type = new ExampleType();
        $attributes = $type->getAttributes();
        $this->assertArrayHasKey('description', $attributes);
    }

    /**
     * Test the toArray method
     *
     * @test
     * @covers ::toArray
     */
    public function testToArray()
    {
        $interfaces = [
            GraphQL::type('ExampleInterface')
        ];
        $this->type->setInterfaces($interfaces);
        $array = $this->type->toArray();
        $fieldsForObjectType = $this->type->getFieldsForObjectType();
        $this->assertArrayHasKey('fields', $array);
        $this->assertArrayHasKey('interfaces', $array);
        $this->assertEquals($this->type->getInterfaces(), $array['interfaces']);
        $this->assertEquals($fieldsForObjectType, $array['fields']());
    }

    /**
     * Test to type
     *
     * @test
     * @covers ::toType
     */
    public function testToType()
    {
        $type = new ExampleType();
        $objectType = $type->toType();

        $this->assertInstanceOf(ObjectType::class, $objectType);
        $this->assertEquals($objectType->name, $type->name);
        $this->assertEquals(array_keys($objectType->getFields()), array_keys($type->getFields()));
    }
}


class ExampleFieldResolver
{
    public function resolve()
    {
    }
}


class ExampleType extends Type
{
    protected function attributes()
    {
        return [
            'name' => 'name',
            'description' => 'description'
        ];
    }

    protected function fields()
    {
        $field = new \App\GraphQL\Field\ExampleField();
        return [
            'test' => [
                'type' => GraphQL::type('Example')
            ],
            'test_no_resolve' => [
                'type' => GraphQL::type('Example')
            ],
            'test_string' => \App\GraphQL\Field\ExampleField::class,
            'test_class' => $field
        ];
    }

    protected function interfaces()
    {
        return [
            GraphQL::type('ExampleInterface')
        ];
    }

    protected function resolveTestField()
    {
        return 'resolveReturn';
    }
}
