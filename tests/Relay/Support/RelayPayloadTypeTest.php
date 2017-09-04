<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Folklore\GraphQL\Relay\Support\PayloadType;
use Folklore\GraphQL\Support\Type as BaseType;

/**
 * @coversDefaultClass \Folklore\GraphQL\Relay\Support\PayloadType
 */
class RelayPayloadTypeTest extends RelayTestCase
{
    protected $type;

    public function setUp()
    {
        parent::setUp();

        $this->type = new PayloadType();
    }

    /**
     * Test that relay PayloadType is a base Type
     *
     * @test
     * @covers ::__construct
     */
    public function testIsInputType()
    {
        $this->assertInstanceOf(BaseType::class, $this->type);
    }

    /**
     * Test that there is a clientMutationId field
     *
     * @test
     */
    public function testHasClientMutationIdField()
    {
        $fields = $this->type->getFields();
        $this->assertArrayHasKey('clientMutationId', $fields);
        $this->assertEquals([
            'type' => Type::nonNull(Type::string())
        ], $fields['clientMutationId']);
    }
}
