<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\NodeIdField;
use Folklore\GraphQL\Support\Field;

/**
 * @coversDefaultClass \Folklore\GraphQL\Relay\NodeIdField
 */
class RelayNodeIdFieldTest extends RelayTestCase
{
    protected $field;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->field = new NodeIdField();
    }

    /**
     * Test is field
     *
     * @test
     * @covers ::__construct
     */
    public function testIsField()
    {
        $this->assertInstanceOf(Field::class, $this->field);
    }

    /**
     * Test type is okay
     *
     * @test
     * @covers ::type
     * @covers ::getType
     */
    public function testTypeIsId()
    {
        $this->assertEquals(Type::nonNull(Type::id()), $this->field->getType());
    }

    /**
     * Test get and set id resolver
     *
     * @test
     * @covers ::setIdResolver
     * @covers ::getIdResolver
     */
    public function testGetIdResolver()
    {
        $resolver = function () {
            return 1;
        };
        $this->field->setIdResolver($resolver);
        $this->assertEquals($resolver, $this->field->getIdResolver());
    }

    /**
     * Test get and set id type
     *
     * @test
     * @covers ::setIdType
     * @covers ::getIdType
     */
    public function testGetIdType()
    {
        $type = 'Type';
        $this->field->setIdType($type);
        $this->assertEquals($type, $this->field->getIdType());
    }

    /**
     * Test is field type
     *
     * @test
     * @covers ::setIdResolver
     * @covers ::setIdType
     * @covers ::resolve
     */
    public function testResolve()
    {
        $id = 1;
        $type = 'Type';
        $resolver = function () use ($id) {
            return $id;
        };
        $this->field->setIdResolver($resolver);
        $this->field->setIdType($type);
        $globalId = Relay::toGlobalId($type, $id);
        $this->assertEquals($this->field->resolve(), $globalId);
    }
}
