<?php

use Folklore\GraphQL\Support\Field;
use GraphQL\Type\Definition\Type;

/**
 * @coversDefaultClass \Folklore\GraphQL\Support\Field
 */
class FieldTest extends TestCase
{
    protected $field;

    public function setUp()
    {
        parent::setUp();

        $this->field = app(Field::class);
    }

    /**
     * Test get and set type
     *
     * @test
     * @covers ::type
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
     * @covers ::args
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
        $rootResolver = $field->getRootResolver();
        $this->assertEquals([$field, 'resolveRoot'], $rootResolver);
        $this->assertEquals('root', $rootResolver());

        $fieldMock = $this->getMockBuilder(ExampleField::class)
            ->setMethods(['resolveRoot'])
            ->getMock();
        $fieldMock->expects($this->once())
            ->method('resolveRoot')
            ->willReturn('returnRoot');
        $rootResolver = $fieldMock->getRootResolver();
        $this->assertEquals('returnRoot', $rootResolver());
    }

    /**
     * Test get and set resolver
     *
     * @test
     * @covers ::getResolver
     * @covers ::setResolver
     */
    public function testGetResolver()
    {
        $this->assertNull($this->field->getResolver());
        $resolver = function ($root) {
            return 'resolve';
        };
        $this->field->setResolver($resolver);
        $this->assertEquals($resolver, $this->field->getResolver());
    }

    /**
     * Test get resolver from resolve method
     *
     * @test
     * @covers ::getResolver
     */
    public function testGetResolverMethod()
    {
        $field = new ExampleField();
        $resolver = $field->getResolver();
        $this->assertInstanceOf(Closure::class, $resolver);
        $this->assertEquals('root+resolve', $resolver('root'));

        $fieldMock = $this->getMockBuilder(ExampleField::class)
            ->setMethods(['resolve'])
            ->getMock();
        $fieldMock->expects($this->once())
            ->method('resolve')
            ->willReturn('returnResolve');
        $resolver = $fieldMock->getResolver();
        $this->assertEquals('returnResolve', $resolver('root'));
    }

    /**
     * Test resolver and root resolver
     *
     * @test
     * @covers ::getResolver
     * @covers ::setRootResolver
     * @covers ::setResolver
     */
    public function testGetResolverAndRoot()
    {
        $rootResolver = function ($root) {
            return 'root';
        };
        $resolver = function ($root) {
            return $root.'+resolve';
        };
        $this->field->setRootResolver($rootResolver);
        $this->field->setResolver($resolver);
        $resolver = $this->field->getResolver();
        $this->assertEquals('root+resolve', $resolver(''));
    }

    /**
     * Test toArray method
     *
     * @test
     * @covers ::toArray
     */
    public function testToArray()
    {
        $field = new ExampleField();
        $array = $field->toArray();
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('args', $array);
        $this->assertArrayHasKey('resolve', $array);
        $this->assertEquals($field->description, $array['description']);
        $this->assertEquals($field->getType(), $array['type']);
        $this->assertEquals($field->getArgs(), $array['args']);
        $this->assertEquals($field->getResolver(), $array['resolve']);
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

    public function resolve($root)
    {
        return $root.'+resolve';
    }
}
