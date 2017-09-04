<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\DocumentValidator;

class ConsoleTestCase extends TestCase
{
    protected $clearPaths = [
        'GraphQL/Type/ConsoleType.php',
        'GraphQL/Type/ConsoleInputType.php',
        'GraphQL/Query/ConsoleQuery.php',
        'GraphQL/Mutation/ConsoleMutation.php',
        'GraphQL/Field/ConsoleField.php',
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

    public function assertGeneratorCommand($path, $name, $extends = 'Base[A-Z][a-z]+')
    {
        require($path);
        $reflection = new ReflectionClass($name);
        $shortName = $reflection->getShortName();
        $this->assertTrue(file_exists($path));
        $contents = file_get_contents($path);
        $this->assertRegExp('/class '.$shortName.' extends '.$extends.'/', $contents);
        $this->assertRegExp('/'.preg_quote('\'name\' => \''.$shortName.'\',').'/', $contents);
    }
}
