<?php namespace Folklore\GraphQL;

use Folklore\GraphQL\Error\ValidationError;
use Folklore\GraphQL\Events\SchemaAdded;
use Folklore\GraphQL\Events\TypeAdded;
use Folklore\GraphQL\Exception\SchemaNotFound;
use Folklore\GraphQL\Exception\TypeNotFound;
use GraphQL\Error\Error;
use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\DocumentValidator;

class GraphQL
{
    /**
     * @var mixed
     */
    protected $app;

    /**
     * @var array
     */
    protected $schemas = [];
    /**
     * @var array
     */
    protected $types = [];
    /**
     * @var array
     */
    protected $typesInstances = [];
    /**
     * @var mixed
     */
    protected $schema;
    /**
     * @var mixed
     */
    protected $introspectionQuery;

    /**
     * @param $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @param $schema
     * @return mixed
     */
    public function schema($schema = null)
    {
        if ($schema instanceof Schema) {
            return $schema;
        }

        //Get the schema
        $schemaName = is_string($schema) ? $schema : $this->getDefaultSchema();
        if (!is_array($schema) && !isset($this->schemas[$schemaName])) {
            throw new SchemaNotFound('Type '.$schemaName.' not found.');
        }
        $schema = is_array($schema) ? $schema : $this->schemas[$schemaName];

        // Get values from the schema
        $schemaQuery    = array_get($schema, 'query', []);
        $schemaMutation = array_get($schema, 'mutation', []);
        $schemaTypes    = array_get($schema, 'types', []);

        // Clear the cache of type instance
        $this->clearTypeInstances();

        // Get the types either from the schema, or the global types.
        $types = [];
        if (sizeof($schemaTypes)) {
            foreach ($schemaTypes as $name => $type) {
                $objectType = $this->objectType($type, is_numeric($name) ? [] : [
                    'name' => $name,
                ]);
                $this->typesInstances[$name] = $objectType;
                $types[]                     = $objectType;
            }
        } else {
            foreach ($this->types as $name => $type) {
                $types[] = $this->type($name);
            }
        }

        // Create the root Query object type
        $query = $this->objectType($schemaQuery, [
            'name' => 'Query',
        ]);

        // Create the root Mutation object type
        $mutation = $this->objectType($schemaMutation, [
            'name' => 'Mutation',
        ]);

        return new Schema([
            'query'    => $query,
            'mutation' => $mutation,
            'types'    => $types,
        ]);
    }

    /**
     * @param $name
     * @param $fresh
     * @return mixed
     */
    public function type($name, $fresh = false)
    {
        if (!isset($this->types[$name])) {
            throw new TypeNotFound('Type '.$name.' not found.');
        }

        if (!$fresh && isset($this->typesInstances[$name])) {
            return $this->typesInstances[$name];
        }

        $class = $this->types[$name];
        $type  = $this->objectType($class, [
            'name' => $name,
        ]);
        $this->typesInstances[$name] = $type;

        return $type;
    }

    /**
     * @param $type
     * @param array $opts
     * @return mixed
     */
    public function objectType($type, $opts = [])
    {
        // If it's already an ObjectType, just update properties and return it.
        // If it's an array, assume it's an array of fields and build ObjectType
        // from it. Otherwise, assume it's a class path or an instance.
        $objectType = null;
        if ($type instanceof ObjectType) {
            $objectType = $type;
            foreach ($opts as $key => $value) {
                if (property_exists($objectType, $key)) {
                    $objectType->{$key} = $value;
                }
                if (isset($objectType->config[$key])) {
                    $objectType->config[$key] = $value;
                }
            }
        } elseif (is_array($type)) {
            $objectType = $this->buildObjectTypeFromFields($type, $opts);
        } else {
            $objectType = $this->buildObjectTypeFromClass($type, $opts);
        }

        return $objectType;
    }

    /**
     * @param $query
     * @param array $params
     * @param array $opts
     */
    public function query($query, $params = [], $opts = [])
    {
        $result = $this->queryAndReturnResult($query, $params, $opts);

        if (!empty($result->errors)) {
            $errorFormatter = config('graphql.error_formatter', [self::class, 'formatError']);

            return [
                'data'   => $result->data,
                'errors' => array_map($errorFormatter, $result->errors),
            ];
        } else {
            return [
                'data' => $result->data,
            ];
        }
    }

    /**
     * @param $query
     * @param array $params
     * @param array $opts
     * @return mixed
     */
    public function queryAndReturnResult($query, $params = [], $opts = [])
    {
        $root          = array_get($opts, 'root', null);
        $context       = array_get($opts, 'context', null);
        $schemaName    = array_get($opts, 'schema', null);
        $operationName = array_get($opts, 'operationName', null);

        $schema = $this->schema($schemaName);

        $result = GraphQLBase::executeAndReturnResult($schema, $query, $root, $context, $params, $operationName);

        return $result;
    }

    /**
     * @param $schema
     * @return mixed
     */
    public function introspection($schema = null)
    {
        if (!$schema) {
            $schema = $this->getDefaultSchema();
        }

        $query = $this->introspectionQuery();

        $queryDepth = $this->getMaxQueryDepth();
        if ($queryDepth < 110) {
            $this->setMaxQueryDepth(110);
        }

        $return = $this->query($query, [], [
            'schema' => $schema,
        ]);

        if ($queryDepth < 110) {
            $this->setMaxQueryDepth($queryDepth);
        }

        return $return;
    }

    /**
     * @return mixed
     */
    public function introspectionQuery()
    {
        if (!$this->introspectionQuery) {
            $this->introspectionQuery = $this->loadIntrospectionQuery();
        }

        return $this->introspectionQuery;
    }

    /**
     * @param $query
     */
    public function setIntrospectionQuery($query)
    {
        $this->introspectionQuery = $query;
    }

    protected function loadIntrospectionQuery()
    {
        $defaultPath       = base_path('resources/graphql/introspectionQuery.txt');
        $introspectionPath = $this->app['config']->get('graphql.introspection.query', $defaultPath);
        if (!file_exists($introspectionPath)) {
            $introspectionPath = __DIR__.'/../../resources/graphql/introspectionQuery.txt';
        }
        return file_get_contents($introspectionPath);
    }

    public function routerSchemaPattern()
    {
        $schemaNames = array_keys($this->getSchemas());
        return '('.implode('|', $schemaNames).')';
    }

    /**
     * @param $types
     */
    public function addTypes($types)
    {
        foreach ($types as $name => $type) {
            $this->addType($type, is_numeric($name) ? null : $name);
        }
    }

    /**
     * @param $class
     * @param $name
     */
    public function addType($class, $name = null)
    {
        $name               = $this->getTypeName($class, $name);
        $this->types[$name] = $class;

        if ($this->app['events']) {
            $this->app['events']->fire(new TypeAdded($class, $name));
        }
    }

    /**
     * @param $schemas
     */
    public function addSchemas($schemas)
    {
        foreach ($schemas as $name => $schema) {
            $this->addSchema($name, $schema);
        }
    }

    /**
     * @param $name
     * @param $schema
     */
    public function addSchema($name, $schema)
    {
        $this->schemas[$name] = $schema;

        if ($this->app['events']) {
            $this->app['events']->fire(new SchemaAdded($schema, $name));
        }
    }

    /**
     * @param $name
     */
    public function clearType($name)
    {
        if (isset($this->types[$name])) {
            unset($this->types[$name]);
        }
    }

    /**
     * @param $name
     */
    public function clearSchema($name)
    {
        if (isset($this->schemas[$name])) {
            unset($this->schemas[$name]);
        }
    }

    public function clearTypes()
    {
        $this->types = [];
    }

    public function clearSchemas()
    {
        $this->schemas = [];
    }

    /**
     * @return mixed
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @return mixed
     */
    public function getSchemas()
    {
        return $this->schemas;
    }

    /**
     * @param $name
     */
    public function setDefaultSchema($name)
    {
        $this->schema = $name;
    }

    /**
     * @return mixed
     */
    public function getDefaultSchema()
    {
        return $this->schema;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setMaxQueryComplexity($value)
    {
        $rule = DocumentValidator::getRule('QueryComplexity');
        $rule->setMaxQueryComplexity($value);
        return $rule;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setMaxQueryDepth($value)
    {
        $rule = DocumentValidator::getRule('QueryDepth');
        $rule->setMaxQueryDepth($value);
        return $rule;
    }

    /**
     * @return mixed
     */
    public function getMaxQueryComplexity()
    {
        $rule = DocumentValidator::getRule('QueryComplexity');
        return $rule->getMaxQueryComplexity();
    }

    /**
     * @return mixed
     */
    public function getMaxQueryDepth()
    {
        $rule = DocumentValidator::getRule('QueryDepth');
        return $rule->getMaxQueryDepth();
    }

    protected function clearTypeInstances()
    {
        $this->typesInstances = [];
    }

    /**
     * @param $type
     * @param array $opts
     * @return mixed
     */
    protected function buildObjectTypeFromClass($type, $opts = [])
    {
        if (!is_object($type)) {
            $type = $this->app->make($type);
        }

        foreach ($opts as $key => $value) {
            $type->{$key} = $value;
        }

        return $type->toType();
    }

    /**
     * @param $fields
     * @param array $opts
     */
    protected function buildObjectTypeFromFields($fields, $opts = [])
    {
        $typeFields = [];
        foreach ($fields as $name => $field) {
            if (is_string($field)) {
                $field       = $this->app->make($field);
                $name        = is_numeric($name) ? $field->name : $name;
                $field->name = $name;
                $field       = $field->toArray();
            } else {
                $name          = is_numeric($name) ? $field['name'] : $name;
                $field['name'] = $name;
            }
            $typeFields[$name] = $field;
        }

        return new ObjectType(array_merge([
            'fields' => $typeFields,
        ], $opts));
    }

    /**
     * @param $class
     * @param $name
     * @return mixed
     */
    protected function getTypeName($class, $name = null)
    {
        if ($name) {
            return $name;
        }

        $type = is_object($class) ? $class : $this->app->make($class);
        return $type->name;
    }

    /**
     * @param Error $e
     * @return mixed
     */
    public static function formatError(Error $e)
    {
        $error = [
            'message' => $e->getMessage(),
        ];

        $locations = $e->getLocations();
        if (!empty($locations)) {
            $error['locations'] = array_map(function ($loc) {
                return $loc->toArray();
            }, $locations);
        }

        $previous = $e->getPrevious();
        if ($previous && $previous instanceof ValidationError) {
            $error['validation'] = $previous->getValidatorMessages();
        }

        return $error;
    }
}
