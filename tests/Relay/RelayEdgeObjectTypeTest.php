<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\EdgeObjectType;

class RelayEdgeObjectTypeTest extends RelayTestCase
{
    /**
     * Test is field
     *
     * @test
     */
    public function testSetEdgeType()
    {
        $type = Type::string();
        $edgeObjectType = new EdgeObjectType([
            'name' => 'Test',
            'fields' => [
                'node' => [
                    'type' => Type::int()
                ]
            ]
        ]);
        $edgeObjectType->setEdgeType($type);
        $this->assertEquals($edgeObjectType->getField('node')->config['type'], $type);
    }
}
