<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\DocumentValidator;

class RelayConsoleTestCase extends RelayTestCase
{
    protected $clearPaths = [
        'GraphQL/Type/ConnectionType.php',
        'GraphQL/Type/InputType.php',
        'GraphQL/Type/PayloadType.php',
        'GraphQL/Type/NodeType.php',
        'GraphQL/Mutation/TestMutation.php',
        'resources/graphql/',
        'GraphQL/Type/',
        'GraphQL/Query/',
        'GraphQL/Mutation/',
        'GraphQL/Field/',
        'GraphQL/'
    ];

    public function tearDown()
    {
        $this->tearDownFiles();
        parent::tearDown();
    }

    public function tearDownFiles()
    {
        foreach ($this->clearPaths as $path) {
            $path = app_path($path);
            if (!file_exists($path)) {
                continue;
            }
            if (is_dir($path)) {
                if (count(scandir($path)) > 2) {
                    continue;
                }
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    }

    public function assertGeneratorCommand($path, $name, $instanceOf, $extends = 'Base[A-Z][a-z]+')
    {
        $this->assertTrue(file_exists($path));

        require($path);
        $reflection = new ReflectionClass($name);
        $shortName = $reflection->getShortName();
        $this->assertInstanceOf($instanceOf, new $name());

        $contents = file_get_contents($path);
        $this->assertRegExp('/class '.$shortName.' extends '.$extends.'/', $contents);
        $this->assertRegExp('/'.preg_quote('\'name\' => \''.$shortName.'\',').'/', $contents);
    }
}
