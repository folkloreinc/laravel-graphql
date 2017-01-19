<?php

use Folklore\GraphQL\Relay\Support\ConnectionField;

/**
 * @coversDefaultClass \Folklore\GraphQL\Relay\Relay
 */
class RelayTest extends RelayTestCase
{
    protected $relay;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->relay = app('graphql.relay');
    }

    /**
     * Test global id methods
     *
     * @test
     * @covers ::toGlobalId
     * @covers ::fromGlobalId
     * @covers ::getIdFromGlobalId
     * @covers ::getTypeFromGlobalId
     */
    public function testGlobalId()
    {
        $type = 'Test';
        $id = 1;
        $globalId = $this->relay->toGlobalId($type, $id);
        $decodedGlobalId = $this->relay->fromGlobalId($globalId);
        $decodedId = $this->relay->getIdFromGlobalId($globalId);
        $decodedType = $this->relay->getTypeFromGlobalId($globalId);
        $this->assertEquals($type, $decodedGlobalId['type']);
        $this->assertEquals($id, $decodedGlobalId['id']);
        $this->assertEquals($decodedGlobalId['id'], $decodedId);
        $this->assertEquals($decodedGlobalId['type'], $decodedType);
    }

    /**
     * Test global id methods
     *
     * @test
     * @covers ::connectionField
     */
    public function testConnectionField()
    {
        $name = 'testConnectionField';
        $field = $this->relay->connectionField([
            'name' => $name
        ]);
        $this->assertInstanceOf(ConnectionField::class, $field);
        $this->assertEquals($name, $field->name);
    }
}
