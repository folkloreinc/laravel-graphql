<?php

use Folklore\Support\Field;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType as UnionObjectType;
use Folklore\GraphQL\Support\UnionType;
use App\GraphQL\Type\ExampleUnionType;

/**
 * @coversDefaultClass \Folklore\GraphQL\Support\UnionType
 */
class UnionTypeTest extends TestCase
{
    protected $type;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('graphql.types', [
            'ExampleInterface' => \App\GraphQL\Type\ExampleInterfaceType::class,
            'Example' => \App\GraphQL\Type\ExampleType::class,
            'CustomExample' => \App\GraphQL\Type\ExampleType::class
        ]);
    }

    public function setUp()
    {
        parent::setUp();

        $this->type = app(UnionType::class);
    }

    /**
     * Test to array
     *
     * @test
     */
    public function testToArray()
    {
        $attributes = $this->type->toArray();
        $this->assertArrayHasKey('types', $attributes);
    }

    /**
     * Test get Types
     *
     * @test
     * @covers ::getTypes
     * @covers ::setTypes
     */
    public function testGetTypes()
    {
        $types = [
            GraphQL::type('Example')
        ];
        $unionMock = $this->getMockBuilder(ExampleUnionType::class)
            ->setMethods(['types'])
            ->getMock();
        $unionMock->expects($this->once())
            ->method('types')
            ->willReturn($types);
        $typeTypes = $unionMock->getTypes();
        $this->assertEquals($typeTypes, $types);

        $types = [
            GraphQL::type('CustomExample')
        ];
        $this->type->setTypes($types);
        $typeTypes = $this->type->getTypes();
        $this->assertEquals($typeTypes, $types);
    }

    /**
     * Test to type
     *
     * @test
     * @covers ::toType
     */
    public function testToType()
    {
        $this->type->name = 'ExampleUnionType';
        $objectType = $this->type->toType();

        $this->assertInstanceOf(UnionObjectType::class, $objectType);

        $this->assertEquals($objectType->name, $this->type->name);

        $typeTypes = $this->type->getTypes();
        $types = $objectType->getTypes();
        $this->assertEquals($typeTypes, $types);
    }
}
