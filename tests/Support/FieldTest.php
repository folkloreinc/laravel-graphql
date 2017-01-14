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
        $this->assertNull($this->field->getType());
        $type = Type::string();
        $this->field->setType($type);
        $this->assertEquals($type, $this->field->getType());

        $field = new ExampleField();
        $this->assertEquals(Type::string(), $field->getType());
    }

    /**
     * Test get and set args
     *
     * @test
     * @covers ::setArgs
     * @covers ::getArgs
     */
    public function testGetArgs()
    {
        $this->assertEquals([], $this->field->getArgs());
        $args = [
            'test' => [
                'name' => 'test',
                'type' => Type::string()
            ]
        ];
        $this->field->setArgs($args);
        $this->assertEquals($args, $this->field->getArgs());

        $field = new ExampleField();
        $this->assertEquals($args, $field->getArgs());
    }

    /**
     * Test the attributes method
     *
     * @test
     * @covers ::attributes
     * @covers ::getAttributes
     * @covers ::__isset
     * @covers ::__get
     */
    public function testGetAttributes()
    {
        $baseAttributes = [
            'description' => 'test'
        ];
        $this->field->description = $baseAttributes['description'];
        $attributes = $this->field->getAttributes();
        $this->assertEquals($baseAttributes, $attributes);
        $this->assertTrue(isset($this->field->description));
        $this->assertEquals($baseAttributes['description'], $this->field->description);

        $field = new ExampleField();
        $attributes = $field->getAttributes();
        $this->assertArrayHasKey('description', $attributes);
        $this->assertEquals('test', $attributes['description']);
    }

    /**
     * Test get and set root resolver
     *
     * @test
     * @covers ::setRootResolver
     * @covers ::getRootResolver
     */
    public function testGetRootResolver()
    {
        $this->assertNull($this->field->getRootResolver());
        $rootResolver = function ($root) {
            return 'root';
        };
        $this->field->setRootResolver($rootResolver);
        $this->assertEquals($rootResolver, $this->field->getRootResolver());
    }

    /**
     * Test root resolver from resolveRoot method
     *
     * @test
     * @covers ::getRootResolver
     */
    public function testGetRootResolverMethod()
    {
        $field = new ExampleField();
        $this->assertEquals([$field, 'resolveRoot'], $field->getRootResolver());
        $this->assertEquals('root', $field->resolveRoot());

        $fieldMock = $this->getMockBuilder(ExampleField::class)
            ->setMethods(['resolveRoot'])
            ->getMock();
        $fieldMock->expects($this->once())
            ->method('resolveRoot')
            ->willReturn('returnRoot');
        $this->assertEquals('returnRoot', $fieldMock->resolveRoot());
    }
}


class ExampleField extends Field
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
            ]
        ];
    }

    protected function attributes()
    {
        return [
            'description' => 'test'
        ];
    }

    public function resolveRoot()
    {
        return 'root';
    }
}
