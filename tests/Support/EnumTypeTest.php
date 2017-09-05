<?php

use Folklore\Support\Field;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\EnumType as EnumObjectType;

use Folklore\GraphQL\Support\EnumType;
use App\GraphQL\Type\ExampleEnumType;

/**
 * @coversDefaultClass \Folklore\GraphQL\Support\EnumType
 */
class EnumTypeTest extends TestCase
{
    protected $type;

    public function setUp()
    {
        parent::setUp();

        $this->type = app(EnumType::class);
    }

    /**
     * Test get attributes
     *
     * @test
     * @covers ::toArray
     */
    public function testGetAttributes()
    {
        $attributes = $this->type->toArray();
        $this->assertArrayHasKey('values', $attributes);
    }

    /**
     * Test get values
     *
     * @test
     * @covers ::getValues
     * @covers ::setValues
     */
    public function testGetValues()
    {
        $values = [
            'TEST' => [
                'value' => 1,
            ]
        ];
        $enumMock = $this->getMockBuilder(ExampleEnumType::class)
            ->setMethods(['values'])
            ->getMock();
        $enumMock->expects($this->once())
            ->method('values')
            ->willReturn($values);
        $typeValues = $enumMock->getValues();
        $this->assertEquals($typeValues, $values);

        $values = [
            'TEST' => [
                'value' => 2,
            ]
        ];
        $this->type->setValues($values);
        $typeValues = $this->type->getValues();
        $this->assertEquals($typeValues, $values);
    }

    /**
     * Test to type
     *
     * @test
     * @covers ::toType
     */
    public function testToType()
    {
        $this->type->name = 'ExampleEnum';
        $this->type->setValues([
            'TEST' => [
                'value' => 1
            ]
        ]);
        $objectType = $this->type->toType();

        $this->assertInstanceOf(EnumObjectType::class, $objectType);

        $this->assertEquals($objectType->name, $this->type->name);

        $typeValues = $this->type->getValues();
        $values = $objectType->getValues();
        $this->assertEquals(array_keys($typeValues)[0], $values[0]->name);
        $this->assertEquals($typeValues['TEST']['value'], $values[0]->value);
    }
}
