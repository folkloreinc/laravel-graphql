<?php

use Folklore\Support\Field;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use Illuminate\Validation\Validator;

class MutationTest extends FieldTest
{
    protected function getFieldClass()
    {
        return \App\GraphQL\Mutation\UpdateExampleMutation::class;
    }
    
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        
        $app['config']->set('graphql.types', [
            'Example' => \App\GraphQL\Type\ExampleType::class
        ]);
    }
    
    /**
     * Test get rules
     *
     * @test
     */
    public function testGetRulesForValidator()
    {
        $class = $this->getFieldClass();
        $field = new $class();
        $rules = $field->getRulesForValidator();
        
        $this->assertInternalType('array', $rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('name_with_rules', $rules);
        $this->assertArrayHasKey('name_with_rules_closure', $rules);
        $this->assertEquals($rules['name'], ['required']);
        $this->assertEquals($rules['name_with_rules'], ['required']);
        $this->assertEquals($rules['name_with_rules_closure'], ['required']);
    }
    
    /**
     * Test resolve
     *
     * @test
     */
    public function testResolve()
    {
        $class = $this->getFieldClass();
        $field = $this->getMockBuilder($class)
                    ->setMethods(['resolve'])
                    ->getMock();

        $field->expects($this->once())
            ->method('resolve');
        
        $attributes = $field->toArray();
        $attributes['resolve'](null, [
            'name' => 'name',
            'name_with_rules' => 'name',
            'name_with_rules_closure' => 'name'
        ], [], null);
    }
    
    /**
     * Test resolve throw validation error
     *
     * @test
     * @expectedException \Folklore\GraphQL\Error\ValidationError
     */
    public function testResolveThrowValidationError()
    {
        $class = $this->getFieldClass();
        $field = new $class();
        
        $attributes = $field->toArray();
        $attributes['resolve'](null, [], [], null);
    }
    
    /**
     * Test validation error
     *
     * @test
     */
    public function testValidationError()
    {
        $class = $this->getFieldClass();
        $field = new $class();
        
        $attributes = $field->toArray();
        
        try {
            $attributes['resolve'](null, [], [], null);
        } catch (\Folklore\GraphQL\Error\ValidationError $e) {
            $validator = $e->getValidator();
            
            $this->assertInstanceOf(Validator::class, $validator);
            
            $messages = $e->getValidatorMessages();
            $this->assertTrue($messages->has('name'));
            $this->assertTrue($messages->has('name_with_rules'));
            $this->assertTrue($messages->has('name_with_rules_closure'));
        }
    }
}
