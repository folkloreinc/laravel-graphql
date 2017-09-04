<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Folklore\GraphQL\Relay\Support\InputType;
use Folklore\GraphQL\Support\InputType as BaseInputType;

/**
 * @coversDefaultClass \Folklore\GraphQL\Relay\Support\InputType
 */
class RelayInputTypeTest extends RelayTestCase
{
    protected $type;

    public function setUp()
    {
        parent::setUp();

        $this->type = new InputType();
    }

    /**
     * Test that relay InputType is a base InputType
     *
     * @test
     * @covers ::__construct
     */
    public function testIsInputType()
    {
        $this->assertInstanceOf(BaseInputType::class, $this->type);
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
