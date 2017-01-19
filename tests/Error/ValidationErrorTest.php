<?php

use Folklore\GraphQL\Error\ValidationError;

/**
 * @coversDefaultClass \Folklore\GraphQL\Error\ValidationError
 */
class ValidationErrorTest extends TestCase
{
    protected $error;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->error = new ValidationError('Test message');
    }

    /**
     * Test that it can set and get validator
     *
     * @test
     * @covers ::setValidator
     * @covers ::getValidator
     */
    public function testGetValidator()
    {
        $this->assertNull($this->error->getValidator());
        $validator = Validator::make([], [
            'test' => 'required',
        ]);
        $this->error->setValidator($validator);
        $this->assertEquals($validator, $this->error->getValidator());
    }

    /**
     * Test that it can get validator messages
     *
     * @test
     * @covers ::getValidatorMessages
     */
    public function testGetValidatorMessages()
    {
        $validator = Validator::make([], [
            'test' => 'required',
        ]);
        $this->error->setValidator($validator);
        $validator->fails();
        $messages = $this->error->getValidatorMessages();
        $this->assertArrayHasKey('test', $messages->getMessages());
    }
}
