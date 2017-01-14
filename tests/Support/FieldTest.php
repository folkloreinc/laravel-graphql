<?php

use Folklore\GraphQL\Support\Field;
use GraphQL\Type\Definition\Type;

/**
 * @coversDefaultClass \Folklore\GraphQL\Support\Field
 */
class FieldTest extends TestCase
{
    protected $field;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->field = app(Field::class);
    }

    /**
     * Test get and set type
     *
     * @test
     * @covers ::setType
     * @covers ::getType
     */
    public function testGetType()
    {
        $this->assertNull(null, $this->field->getType());
        $type = Type::string();
        $this->field->setType($type);
        $this->assertEquals($type, $this->field->getType());
    }
}
