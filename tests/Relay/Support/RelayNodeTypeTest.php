<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\Support\NodeType;
use Folklore\GraphQL\Support\Type as BaseType;

/**
 * @coversDefaultClass \Folklore\GraphQL\Relay\Support\NodeType
 */
class RelayNodeTypeTest extends RelayTestCase
{
    protected $type;

    public function setUp()
    {
        parent::setUp();

        $this->type = new \App\GraphQL\Relay\Type\ExampleNodeType();
    }

    /**
     * Test that relay NodeType is a base Type
     *
     * @test
     * @covers ::__construct
     */
    public function testIsBaseType()
    {
        $this->assertInstanceOf(BaseType::class, $this->type);
    }

    /**
     * Test that the node as the Node interface
     *
     * @test
     * @covers ::relayInterfaces
     * @covers ::getInterfaces
     */
    public function testHasNodeInterface()
    {
        $typeArray = $this->type->toArray();

        $this->assertEquals($typeArray['interfaces'][0], GraphQL::type('Node'));
    }

    /**
     * Test that the type has an id field
     *
     * @test
     * @covers ::getFieldsForObjectType
     */
    public function testHasIdField()
    {
        $fields = $this->type->getFields();
        $this->assertArrayHasKey('id', $fields);
        $this->assertEquals($fields['id']['type'], Type::nonNull(Type::id()));
    }
}
