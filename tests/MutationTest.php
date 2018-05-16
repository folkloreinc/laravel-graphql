<?php

use Illuminate\Validation\Validator;

class MutationTest extends FieldTest
{
    protected function getFieldClass()
    {
        return ExampleMutation::class;
    }

    /**
     * Laravel based validation rules should be correctly constructed.
     *
     * @test
     */
    public function testGetRules()
    {
        $field = $this->getFieldInstance();
        $rules = $field->getRules();

        $this->assertInternalType('array', $rules);

        // Those three definitions must have the same result
        $this->assertEquals(['email'], $rules['email_seperate_rules']);
        $this->assertEquals(['email'], $rules['email_inline_rules']);
        $this->assertEquals(['email'], $rules['email_closure_rules']);

        // Apply array validation to values defined as GraphQL-Lists
        $this->assertEquals(['email'], $rules['email_list.*']);
        $this->assertEquals(['email'], $rules['email_list_of_lists.*.*']);

        // Inferred rules from Input Objects
        $this->assertEquals(['alpha'], $rules['input_object.alpha']);
        $this->assertEquals(['email'], $rules['input_object.child.email']);
        $this->assertEquals(['email'], $rules['input_object.child-list.*.email']);

        // Self-referencing, nested InputObject
        $this->assertEquals(['alpha'], $rules['input_object.self.alpha']);
        $this->assertEquals(['email'], $rules['input_object.self.child.email']);
        $this->assertEquals(['email'], $rules['input_object.self.child-list.*.email']);

        // Go down a few levels
        $this->assertEquals(['alpha'], $rules['input_object.self.self.self.self.alpha']);
        $this->assertEquals(['email'], $rules['input_object.self.self.self.self.child.email']);
        $this->assertEquals(['email'], $rules['input_object.self.self.self.self.child-list.*.email']);

        $this->assertArrayNotHasKey(
            'input_object.self.self.self.self.self.self.self.self.self.self.alpha',
            $rules,
            'Validation rules should not be set for such deep nesting.');
    }

    /**
     * Test resolve throw validation error.
     *
     * @test
     */
    public function testResolveThrowValidationError()
    {
        $field = $this->getFieldInstance();
        $attributes = $field->getAttributes();

        $this->expectException('\Folklore\GraphQL\Error\ValidationError');
        $attributes['resolve'](null, [
            'email_inline_rules' => 'not-an-email'
        ], [], null);
    }

    /**
     * Validation error messages are correctly constructed and thrown.
     *
     * @test
     */
    public function testValidationErrorMessages()
    {
        $field = $this->getFieldInstance();
        $attributes = $field->getAttributes();

        try {
            $attributes['resolve'](null, [
                'email_inline_rules' => 'not-an-email',
                'email_list' => ['not-an-email', 'valid@email.com'],
                'email_list_of_lists' => [
                    ['valid@email.com'],
                    ['not-an-email'],
                ],
                'input_object' => [
                    'child' => [
                        'email' => 'not-an-email'
                    ],
                    'self' => [
                        'self' => [
                            'alpha' => 'Not alphanumeric !"§)'
                        ]
                    ]
                ]
            ], [], null);
        } catch (\Folklore\GraphQL\Error\ValidationError $e) {
            $validator = $e->getValidator();

            $this->assertInstanceOf(Validator::class, $validator);

            /** @var \Illuminate\Support\MessageBag $messages */
            $messages = $e->getValidatorMessages();
            $messageKeys = $messages->keys();
            // Ensure that validation errors occurred only where necessary, ignoring the order
            $this->assertEquals([
                'email_inline_rules',
                'email_list.0',
                'email_list_of_lists.1.0',
                'input_object.child.email',
                'input_object.self.self.alpha',
            ], $messageKeys, 'Not all the right fields were validated.', 0, 10, true);

            // The custom validation error message should override the default
            $this->assertEquals('Has to be a valid email.', $messages->first('email_inline_rules'));
            $this->assertEquals('Invalid email: not-an-email', $messages->first('input_object.child.email'));

        }
    }
}
