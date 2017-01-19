<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\NodeInterface;
use Folklore\GraphQL\Relay\NodeResponse;
use Folklore\GraphQL\Support\InterfaceType;

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
     */
    public function testIsInterfaceType()
    {
        $this->assertInstanceOf(InterfaceType::class, $this->interface);
    }

    /**
     * Test has id field
     *
     * @test
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
     * Test has id field
     *
     * @test
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
     * Test has id field
     *
     * @test
     * @expectedException \Folklore\GraphQL\Relay\Exception\NodeRootInvalid
     */
    public function testResolveTypeFromOther()
    {
        $typeResolver = $this->interface->getTypeResolver();
        $typeResolver('root');
    }
}
