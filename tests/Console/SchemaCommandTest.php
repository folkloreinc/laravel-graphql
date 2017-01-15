<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\DocumentValidator;

class SchemaCommandTest extends ConsoleTestCase
{
    public function testSchema()
    {
        $exitCode = Artisan::call('graphql:schema');

        $path = base_path('resources/graphql/schema.json');
        $this->assertTrue(file_exists($path));
        $contents = file_get_contents($path);
        $this->assertEquals(json_decode($contents, true), GraphQL::introspection());
    }
}
