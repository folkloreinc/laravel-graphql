<?php

class MutationTest extends FieldTest
{
    protected function getFieldClass()
    {
        return ExampleMutation::class;
    }

    /**
     * Test resolve throw validation error.
     *
     * @expectedException \Folklore\GraphQL\Error\ValidationError
     * @test
     */
    public function testResolveThrowValidationError()
    {
        $this->callResolveWithInput([
            'email_inline_rules' => 'not-an-email'
        ]);
    }

    /**
     * Validation error messages are correctly constructed and thrown.
     *
     * @test
     */
    public function testCustomValidationErrorMessages()
    {
        try {
            $this->callResolveWithInput([
                'email_inline_rules' => 'not-an-email',
                'input_object' => [
                    'child' => [
                        'email' => 'not-an-email'
                    ],
                ]
            ]);
        } catch (\Folklore\GraphQL\Error\ValidationError $e) {
            $messages = $e->getValidatorMessages();

            // The custom validation error message should override the default
            $this->assertEquals('Has to be a valid email.', $messages->first('email_inline_rules'));
            $this->assertEquals('Invalid email: not-an-email', $messages->first('input_object.child.email'));
        }
    }

    /**
     * @test
     */
    public function testArrayValidationIsApplied()
    {
        try {
            $this->callResolveWithInput([
                'email_list' => ['not-an-email', 'valid@email.com'],
                'email_list_of_lists' => [
                    ['valid@email.com'],
                    ['not-an-email'],
                ],
            ]);
        } catch (\Folklore\GraphQL\Error\ValidationError $e) {
            $messages = $e->getValidatorMessages();

            $messageKeys = $messages->keys();
            $expectedKeys = [
                'email_list.0',
                'email_list_of_lists.1.0',
            ];
            // Sort the arrays before comparison so that order does not matter
            sort($expectedKeys);
            sort($messageKeys);
            // Ensure that validation errors occurred only where necessary
            $this->assertEquals($expectedKeys, $messageKeys, 'Not all the right fields were validated.');
        }
    }

    /**
     * @test
     */
    public function testRulesForNestedInputObjects()
    {
        try {
            $this->callResolveWithInput([
                'input_object' => [
                    'child' => [
                        'email' => 'not-an-email'
                    ],
                    'self' => [
                        'self' => [
                            'alpha' => 'Not alphanumeric !"§)'
                        ],
                        'child_list' => [
                            ['email' => 'abc'],
                            ['email' => 'def']
                        ]
                    ]
                ]
            ]);
        } catch (\Folklore\GraphQL\Error\ValidationError $e) {
            $validator = $e->getValidator();
            $rules = $validator->getRules();

            $this->assertEquals(['email'], $rules['input_object.child.email']);
            $this->assertEquals(['alpha'], $rules['input_object.self.self.alpha']);
            $this->assertEquals(['email'], $rules['input_object.self.child_list.0.email']);
            $this->assertEquals(['email'], $rules['input_object.self.child_list.1.email']);
        }
    }

    /**
     * @test
     */
    public function testExplicitRulesOverwriteInlineRules()
    {
        try {
            $this->callResolveWithInput([
                'email_seperate_rules' => 'asdf'
            ]);
        } catch (\Folklore\GraphQL\Error\ValidationError $e) {
            $validator = $e->getValidator();
            $rules = $validator->getRules();

            $this->assertEquals(['email'], $rules['email_seperate_rules']);
        }
    }

    /**
     * @test
     */
    public function testCanValidateArraysThroughSeperateRules()
    {
        try {
            $this->callResolveWithInput([
                'email_list' => [
                    'invalid',
                    'asdf@asdf.de',
                    'asdf@asdf.de',
                ]
            ]);
        } catch (\Folklore\GraphQL\Error\ValidationError $e) {
            $validator = $e->getValidator();
            $rules = $validator->getRules();

            $this->assertEquals(['max:2'], $rules['email_list']);
            $this->assertEquals(['email'], $rules['email_list.0']);
            $this->assertEquals(['email'], $rules['email_list.1']);
            $this->assertEquals(['email'], $rules['email_list.2']);

            $messages = $e->getValidatorMessages();
            $this->assertEquals('The email list may not be greater than 2 characters.', $messages->first('email_list'));
            $this->assertEquals('The email_list.0 must be a valid email address.', $messages->first('email_list.0'));
        }
    }

    /**
     * @test
     */
    public function testRequiredWithRule()
    {
        try {
            $this->callResolveWithInput([
                'required' => 'whatever'
            ]);
        } catch (\Folklore\GraphQL\Error\ValidationError $e) {
            $validator = $e->getValidator();
            $rules = $validator->getRules();

            $this->assertEquals(['required_with:required'], $rules['required_with']);

            $messages = $e->getValidatorMessages();
            $this->assertEquals('The required with field is required when required is present.', $messages->first('required_with'));
        }
    }

    protected function callResolveWithInput($input)
    {
        $field = $this->getFieldInstance();
        $attributes = $field->getAttributes();

        $attributes['resolve'](null, $input, [], new \GraphQL\Type\Definition\ResolveInfo([]));
    }
}
