<?php namespace Folklore\GraphQL;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Schema;
use GraphQL\Error;

use GraphQL\Type\Definition\ObjectType;

use Folklore\GraphQL\Error\ValidationError;

use Folklore\GraphQL\Exception\TypeNotFound;
use Folklore\GraphQL\Exception\SchemaNotFound;

use Folklore\GraphQL\Events\SchemaAdded;
use Folklore\GraphQL\Events\TypeAdded;

class GraphQL
{
    
    protected $app;
    
    protected $schemas = [];
    protected $types = [];
    protected $typesInstances = [];
    
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function schema($schema = null)
    {
        if ($schema instanceof Schema) {
            return $schema;
        }
        
        $this->clearTypeInstances();
        
        $schemaName = is_string($schema) ? $schema:config('graphql.schema', 'default');
        
        if (!is_array($schema) && !isset($this->schemas[$schemaName])) {
            throw new SchemaNotFound('Type '.$schemaName.' not found.');
        }
        
        $schema = is_array($schema) ? $schema:$this->schemas[$schemaName];
        
        $schemaQuery = array_get($schema, 'query', []);
        $schemaMutation = array_get($schema, 'mutation', []);
        $schemaTypes = array_get($schema, 'types', []);
        
        //Get the types either from the schema, or the global types.
        $types = [];
        if (sizeof($schemaTypes)) {
            foreach ($schemaTypes as $type) {
                if (is_string($type) && isset($this->types[$type])) {
                    $types[] = $this->type($name);
                } else {
                    $types[] = $this->buildObjectTypeFromClass($type);
                }
            }
        } else {
            foreach ($this->types as $name => $type) {
                $types[] = $this->type($name);
            }
        }
        
        $query = $this->objectType($schemaQuery, [
            'name' => 'Query'
        ]);
        
        $mutation = $this->objectType($schemaMutation, [
            'name' => 'Mutation'
        ]);
        
        return new Schema([
            'query' => $query,
            'mutation' => $mutation,
            'types' => $types
        ]);
    }
    
    public function type($name, $fresh = false)
    {
        if (!isset($this->types[$name])) {
            throw new TypeNotFound('Type '.$name.' not found.');
        }
        
        if (!$fresh && isset($this->typesInstances[$name])) {
            return $this->typesInstances[$name];
        }
        
        $class = $this->types[$name];
        $type = $this->objectType($class, [
            'name' => $name
        ]);
        $this->typesInstances[$name] = $type;
        
        return $type;
    }
    
    public function objectType($type, $opts = [])
    {
        // If it's already an ObjectType, just update properties and return it.
        // If it's an array, assume it's an array of fields and build ObjectType
        // from it. Otherwise, build it from a string or an instance.
        $objectType = null;
        if ($type instanceof ObjectType) {
            $objectType = $type;
            foreach ($opts as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        } elseif (is_array($type)) {
            $objectType = $this->buildObjectTypeFromFields($type, $opts);
        } else {
            $objectType = $this->buildObjectTypeFromClass($type, $opts);
        }
        
        return $objectType;
    }
    
    public function query($query, $params = [], $context = null, $schema = null)
    {
        $schema = $this->schema($schema);
        $result = GraphQLBase::executeAndReturnResult($schema, $query, null, $context, $params);
        
        if (!empty($result->errors)) {
            $errorFormatter = config('graphql.error_formatter', ['\Folklore\GraphQL', 'formatError']);
            
            return [
                'data' => $result->data,
                'errors' => array_map($errorFormatter, $result->errors)
            ];
        } else {
            return [
                'data' => $result->data
            ];
        }
    }
    
    public function queryAndReturnResult($query, $params = [], $context = null, $schema = null)
    {
        $schema = $this->schema($schema);
        $result = GraphQLBase::executeAndReturnResult($schema, $query, null, $context, $params);
        return $result;
    }
    
    public function addType($class, $name = null)
    {
        $name = $this->getTypeName($class, $name);
        $this->types[$name] = $class;
        
        $this->app['events']->fire(new TypeAdded($class, $name));
    }
    
    public function addSchema($name, $schema)
    {
        $this->schemas[$name] = $schema;
        
        $this->app['events']->fire(new SchemaAdded($schema, $name));
    }
    
    public function clearType($name)
    {
        if (isset($this->types[$name])) {
            unset($this->types[$name]);
        }
    }
    
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
    
    public function getTypes()
    {
        return $this->types;
    }
    
    public function getSchemas()
    {
        return $this->schemas;
    }
    
    protected function clearTypeInstances()
    {
        $this->typesInstances = [];
    }
    
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
    
    protected function buildObjectTypeFromFields($fields, $opts = [])
    {
        $typeFields = [];
        foreach ($fields as $name => $field) {
            if (is_string($field)) {
                $field = $this->app->make($field);
                $name = is_numeric($name) ? $field->name:$name;
                $field->name = $name;
                $field = $field->toArray();
            } else {
                $name = is_numeric($name) ? $field['name']:$name;
                $field['name'] = $name;
            }
            $typeFields[$name] = $field;
        }
        
        return new ObjectType(array_merge([
            'fields' => $typeFields
        ], $opts));
    }
    
    protected function getTypeName($class, $name = null)
    {
        if ($name) {
            return $name;
        }
        
        $type = is_object($class) ? $class:$this->app->make($class);
        return $type->name;
    }
    
    public static function formatError(Error $e)
    {
        $error = [
            'message' => $e->getMessage()
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
