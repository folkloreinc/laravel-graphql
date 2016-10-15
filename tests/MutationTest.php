<?php

use Folklore\Support\Field;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use Illuminate\Validation\Validator;

class MutationTest extends FieldTest
{
    protected function getFieldClass()
    {
        return UpdateExampleMutation::class;
    }
    
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        
        $app['config']->set('graphql.types', [
            'Example' => ExampleType::class
        ]);
    }
    
    /**
     * Test get rules
     *
     * @test
     */
    public function testGetRules()
    {
        $class = $this->getFieldClass();
        $field = new $class();
        $rules = $field->getRules();
        
        $this->assertInternalType('array', $rules);
        $this->assertArrayHasKey('test', $rules);
        $this->assertArrayHasKey('test_with_rules', $rules);
        $this->assertArrayHasKey('test_with_rules_closure', $rules);
        $this->assertEquals($rules['test'], ['required']);
        $this->assertEquals($rules['test_with_rules'], ['required']);
        $this->assertEquals($rules['test_with_rules_closure'], ['required']);
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
        
        $attributes = $field->getAttributes();
        $attributes['resolve'](null, [
            'test' => 'test',
            'test_with_rules' => 'test',
            'test_with_rules_closure' => 'test'
        ], [], null);
    }
    
    /**
     * Test resolve throw validation error
     *
     * @test
     */
    public function testResolveThrowValidationError()
    {
        $class = $this->getFieldClass();
        $field = new $class();
        
        $this->expectException(\Folklore\GraphQL\Error\ValidationError::class);
        
        $attributes = $field->getAttributes();
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
        
        $attributes = $field->getAttributes();
        
        try {
            $attributes['resolve'](null, [], [], null);
        } catch (\Folklore\GraphQL\Error\ValidationError $e) {
            $validator = $e->getValidator();
            
            $this->assertInstanceOf(Validator::class, $validator);
            
            $messages = $e->getValidatorMessages();
            $this->assertTrue($messages->has('test'));
            $this->assertTrue($messages->has('test_with_rules'));
            $this->assertTrue($messages->has('test_with_rules_closure'));
        }
    }
}
