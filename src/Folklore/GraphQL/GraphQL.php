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

class GraphQL {
    
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
        if($schema instanceof Schema)
        {
            return $schema;
        }
        
        $this->typesInstances = [];
        
        $schemaName = is_string($schema) ? $schema:config('graphql.schema', 'default');
        
        if(!is_array($schema) && !isset($this->schemas[$schemaName]))
        {
            throw new SchemaNotFound('Type '.$schemaName.' not found.');
        }
        
        $schema = is_array($schema) ? $schema:$this->schemas[$schemaName];
        
        $schemaQuery = array_get($schema, 'query', []);
        $schemaMutation = array_get($schema, 'mutation', []);
        $schemaTypes = array_get($schema, 'types', []);
        
        //Get the types either from the schema, or the global types.
        $types = [];
        if(sizeof($schemaTypes))
        {
            foreach($schemaTypes as $type)
            {
                if(is_string($type) && isset($this->types[$type]))
                {
                    $types[] = $this->type($name);
                }
                else
                {
                    $types[] = $this->buildObjectTypeFromClass($type);
                }
            }
        }
        else
        {
            foreach($this->types as $name => $type)
            {
                $types[] = $this->type($name);
            }
        }
        
        // Get the query ObjectType. If it's a string, assume it's a class
        // name. If it's an object, check if it's an ObjectType, if not assume
        // it's a Query object, otherwise assume it's an array of fields and
        // build the ObjectType from it.
        if(is_string($schemaQuery))
        {
            $query = $this->app->make($schemaQuery)->toType();
        }
        else if(is_object($schemaQuery))
        {
            $query = $schemaQuery instanceof ObjectType ? $schemaQuery:$schemaQuery->toType();
        }
        else
        {
            $query = $this->buildObjectTypeFromFields($schemaQuery, [
                'name' => 'Query'
            ]);
        }
        
        // Get the mutation ObjectType. If it's a string, assume it's a class
        // name. If it's an object, check if it's an ObjectType, if not assume
        // it's a Mutation object, otherwise assume it's an array of fields and
        // build the ObjectType from it.
        if(is_string($schemaMutation))
        {
            $mutation = $this->app->make($schemaMutation)->toType();
        }
        else if(is_object($schemaQuery))
        {
            $mutation = $schemaMutation instanceof ObjectType ? $schemaMutation:$schemaMutation->toType();
        }
        else
        {
            $mutation = $this->buildObjectTypeFromFields($schemaMutation, [
                'name' => 'Mutation'
            ]);
        }
        
        return new Schema([
            'query' => $query,
            'mutation' => $mutation,
            'types' => $types
        ]);
    }
    
    public function type($name, $fresh = false)
    {
        if(!isset($this->types[$name]))
        {
            throw new TypeNotFound('Type '.$name.' not found.');
        }
        
        if(!$fresh && isset($this->typesInstances[$name]))
        {
            return $this->typesInstances[$name];
        }
        
        $class = $this->types[$name];
        $type = $this->buildObjectTypeFromClass($class);
        $this->typesInstances[$name] = $type;
        
        return $type;
    }
    
    public function query($query, $params = [], $context = null, $schema = null)
    {
        $schema = $this->schema($schema);
        $result = GraphQLBase::executeAndReturnResult($schema, $query, null, $context, $params);
        
        if (!empty($result->errors))
        {
            $errorFormatter = config('graphql.error_formatter', ['\Folklore\GraphQL', 'formatError']);
            
            return [
                'data' => $result->data,
                'errors' => array_map($errorFormatter, $result->errors)
            ];
        }
        else
        {
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
    
    protected function buildObjectTypeFromClass($type)
    {
        if(!is_object($type))
        {
            $type = $this->app->make($type);
        }
        
        return $type->toType();
    }
    
    protected function buildObjectTypeFromFields($fields, $opts = [])
    {
        $typeFields = [];
        foreach($fields as $name => $field)
        {
            $field = is_string($field) ? $this->app->make($field)->toArray():$field;
            if(is_numeric($name))
            {
                $name = $field['name'];
            }
            else
            {
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
        if($name)
        {
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
        if(!empty($locations))
        {
            $error['locations'] = array_map(function($loc)
            {
                return $loc->toArray();
            }, $locations);
        }
        
        $previous = $e->getPrevious();
        if($previous && $previous instanceof ValidationError)
        {
            $error['validation'] = $previous->getValidatorMessages();
        }
        
        return $error;
    }
}
