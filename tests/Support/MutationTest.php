<?php

use Folklore\GraphQL\Support\Mutation;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Error\ValidationError;
use Illuminate\Validation\Validator;

/**
 * @coversDefaultClass \Folklore\GraphQL\Support\Mutation
 */
class MutationTest extends TestCase
{
    protected $field;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->field = app(Mutation::class);
    }

    /**
     * Test get and set rules
     *
     * @test
     * @covers ::setRules
     * @covers ::getRules
     */
    public function testGetRules()
    {
        $this->assertNull($this->field->getRules());
        $rules = [
            'test' => ['required']
        ];
        $this->field->setRules($rules);
        $this->assertEquals($rules, $this->field->getRules());
    }

    /**
     * Test resolver with validation
     *
     * @test
     * @covers ::getRulesForValidator
     * @covers ::getValidator
     * @covers ::getResolver
     */
    public function testGetResolver()
    {
        $field = new ExampleMutation();
        $resolver = $field->getResolver();
        $return = $resolver(null, [
            'test' => 'test',
            'validation' => 'test',
            'validation_closure_email' => 'test@test.com'
        ]);
        $this->assertEquals('resolve', $return);
    }

    /**
     * Test resolver with validation error
     *
     * @test
     * @expectedException \Folklore\GraphQL\Error\ValidationError
     * @covers ::getRulesForValidator
     * @covers ::getValidator
     * @covers ::getResolver
     */
    public function testGetResolverThrowException()
    {
        $field = new ExampleMutation();
        $resolver = $field->getResolver();
        $resolver(null, []);
    }

    /**
     * Test validation error
     *
     * @test
     * @covers ::getRulesForValidator
     * @covers ::getValidator
     * @covers ::getResolver
     */
    public function testGetResolverWithValidationError()
    {
        $rules = [
            'test' => ['required'],
            'validation' => ['required'],
            'validation_closure_email' => ['required', 'email']
        ];
        $field = new ExampleMutation();
        $resolver = $field->getResolver();
        try {
            $resolver(null, []);
        } catch (ValidationError $e) {
            $validator = $e->getValidator();
            $this->assertInstanceOf(Validator::class, $validator);
            $this->assertEquals($rules, $validator->getRules());
            $this->assertTrue($validator->errors()->has('test'));
        }
    }
}

class ExampleMutation extends Mutation
{
    protected function type()
    {
        return Type::string();
    }

    protected function args()
    {
        return [
            'test' => [
                'name' => 'test',
                'type' => Type::string()
            ],
            'validation' => [
                'name' => 'test',
                'type' => Type::string(),
                'rules' => [
                    'required'
                ]
            ],
            'validation_closure_email' => [
                'name' => 'test',
                'type' => Type::string(),
                'rules' => function () {
                    return [
                        'required',
                        'email'
                    ];
                }
            ]
        ];
    }

    protected function rules()
    {
        return [
            'test' => ['required']
        ];
    }

    protected function attributes()
    {
        return [
            'description' => 'test'
        ];
    }

    public function resolve($root)
    {
        return 'resolve';
    }
}
