<?php

use Folklore\Support\Field;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;

class UnionTypeTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('graphql.types', [
            'Example' => ExampleType::class,
            'CustomExample' => ExampleType::class
        ]);
    }

    /**
     * Test get attributes
     *
     * @test
     */
    public function testGetAttributes()
    {
        $type = new ExampleUnionType();
        $attributes = $type->getAttributes();

        $this->assertArrayHasKey('resolveType', $attributes);
        $this->assertInstanceOf(Closure::class, $attributes['resolveType']);
    }

    /**
     * Test get attributes resolve type
     *
     * @test
     */
    public function testGetAttributesResolveType()
    {
        $type = $this->getMockBuilder(ExampleUnionType::class)
                    ->setMethods(['resolveType'])
                    ->getMock();

        $type->expects($this->once())
            ->method('resolveType');

        $attributes = $type->getAttributes();
        $attributes['resolveType'](null);
    }

    /**
     * Test to type
     *
     * @test
     */
    public function testGetTypes()
    {
        $type = new ExampleUnionType();
        $typeTypes = $type->getTypes();
        $types = [
            GraphQL::type('Example')
        ];
        $this->assertEquals($typeTypes, $types);

        $type = new ExampleUnionType();
        $types = [
            GraphQL::type('CustomExample')
        ];
        $type->types = $types;
        $typeTypes = $type->getTypes();
        $this->assertEquals($typeTypes, $types);
    }

    /**
     * Test to type
     *
     * @test
     */
    public function testToType()
    {
        $type = new ExampleUnionType();
        $objectType = $type->toType();

        $this->assertInstanceOf(UnionType::class, $objectType);

        $this->assertEquals($objectType->name, $type->name);

        $typeTypes = $type->getTypes();
        $types = $objectType->getTypes();
        $this->assertEquals($typeTypes, $types);
    }
}
