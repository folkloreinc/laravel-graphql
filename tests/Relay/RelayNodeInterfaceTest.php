<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\NodeInterface;
use Folklore\GraphQL\Relay\NodeResponse;
use Folklore\GraphQL\Support\InterfaceType;

/**
 * @coversDefaultClass \Folklore\GraphQL\Relay\NodeInterface
 */
class RelayNodeInterfaceTest extends RelayTestCase
{
    protected $interface;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->interface = new NodeInterface();
    }

    /**
     * Test is interface type
     *
     * @test
     * @covers ::__construct
     */
    public function testIsInterfaceType()
    {
        $this->assertInstanceOf(InterfaceType::class, $this->interface);
    }

    /**
     * Test has id field
     *
     * @test
     * @covers ::fields
     * @covers ::getFields
     */
    public function testHasIdField()
    {
        $fields = $this->interface->getFields();
        $this->assertArrayHasKey('id', $fields);
        $this->assertEquals($fields['id'], [
            'type' => Type::nonNull(Type::id())
        ]);
    }

    /**
     * Test resolving a node response
     *
     * @test
     * @covers ::resolveType
     * @covers ::getTypeResolver
     */
    public function testResolveTypeFromNodeResponse()
    {
        $type = GraphQL::type('Example');
        $response = new NodeResponse();
        $response->setType($type);

        $typeResolver = $this->interface->getTypeResolver();
        $this->assertEquals($typeResolver($response), $type);
    }

    /**
     * Test that resolving other than a NodeResponse throw and exception
     *
     * @test
     * @covers ::resolveType
     * @expectedException \Folklore\GraphQL\Relay\Exception\NodeRootInvalid
     */
    public function testResolveTypeFromOther()
    {
        $typeResolver = $this->interface->getTypeResolver();
        $typeResolver('root');
    }
}
