<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\ConnectionEdgeType;
use Folklore\GraphQL\Relay\EdgeObjectType;

/**
 * @coversDefaultClass \Folklore\GraphQL\Relay\ConnectionEdgeType
 */
class RelayConnectionEdgeTypeTest extends RelayTestCase
{
    protected $edgeType;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->edgeType = new ConnectionEdgeType([
            'name' => 'Test'
        ]);
    }

    /**
     * Test edge fields
     *
     * @test
     * @covers ::fields
     */
    public function testFields()
    {
        $fields = $this->edgeType->getFields();

        $this->assertEquals([
            'cursor' => [
                'type' => Type::nonNull(Type::id())
            ],
            'node' => [
                'type' => GraphQL::type('Node')
            ]
        ], $fields);
    }

    /**
     * Test toType method
     *
     * @test
     * @covers ::toType
     */
    public function testToType()
    {
        $type = $this->edgeType->toType();

        $this->assertInstanceOf(EdgeObjectType::class, $type);
        $this->assertEquals([
            'name' => 'cursor',
            'type' => Type::nonNull(Type::id())
        ], $type->getField('cursor')->config);
    }
}
