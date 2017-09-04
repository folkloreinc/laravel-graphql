<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\Support\Mutation;
use Folklore\GraphQL\Relay\MutationResponse;
use Folklore\GraphQL\Support\Mutation as BaseMutation;

/**
 * @coversDefaultClass \Folklore\GraphQL\Relay\Support\Mutation
 */
class RelayMutationTest extends RelayTestCase
{
    protected $mutation;

    public function setUp()
    {
        parent::setUp();

        $this->mutation = new Mutation();
    }

    /**
     * Test that relay Mutation is a base Mutation
     *
     * @test
     * @covers ::__construct
     */
    public function testIsBaseMutation()
    {
        $this->assertInstanceOf(BaseMutation::class, $this->mutation);
    }

    /**
     * Test set and get input type
     *
     * @test
     * @covers ::inputType
     * @covers ::getInputType
     * @covers ::setInputType
     */
    public function testGetInputType()
    {
        $this->assertNull($this->mutation->getInputType());
        $type = Type::string();
        $this->mutation->setInputType($type);
        $this->assertEquals($type, $this->mutation->getInputType());

        $mutation = new ExampleRelayMutation();
        $this->assertEquals($type, $mutation->getInputType());
    }

    /**
     * Test args contains input type
     *
     * @test
     * @covers ::args
     * @covers ::getArgs
     */
    public function testGetArgs()
    {
        $type = Type::string();
        $this->mutation->setInputType($type);
        $this->assertEquals([
            'input' => [
                'name' => 'input',
                'type' => $type
            ]
        ], $this->mutation->getArgs());
    }

    /**
     * Test that resolver return mutation response with clientMutationId
     *
     * @test
     * @covers ::getResolver
     * @covers ::getMutationResponse
     * @covers ::resolveClientMutationId
     */
    public function testGetResolver()
    {
        $resolver = function ($root) {
            return $root.'+test';
        };
        $args = [
            'input' => [
                'clientMutationId' => 1
            ]
        ];
        $this->mutation->setResolver($resolver);
        $resolver = $this->mutation->getResolver();
        $response = $resolver('resolve', $args);

        $this->assertInstanceOf(MutationResponse::class, $response);
        $this->assertEquals('resolve+test', $response->getOriginalNode());
        $this->assertEquals(1, $response->getClientMutationId());
    }
}

class ExampleRelayMutation extends Mutation
{
    protected function inputType()
    {
        return Type::string();
    }
}
