<?php namespace Folklore\GraphQL;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Schema;
use GraphQL\Error\Error;

use GraphQL\Type\Definition\ObjectType;

use Folklore\GraphQL\Error\ValidationError;

use Folklore\GraphQL\Exception\TypeNotFound;
use Folklore\GraphQL\Exception\SchemaNotFound;

use Folklore\GraphQL\Events\SchemaAdded;
use Folklore\GraphQL\Events\TypeAdded;

use GraphQL\Language\Parser;
use GraphQL\Language\Source;
use GraphQL\Language\Visitor;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\OperationDefinitionNode;


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
        $schemaSubscription = array_get($schema, 'subscription', []);
        $schemaTypes = array_get($schema, 'types', []);
        
        //Get the types either from the schema, or the global types.
        $types = [];
        if (sizeof($schemaTypes)) {
            foreach ($schemaTypes as $name => $type) {
                $objectType = $this->objectType($type, is_numeric($name) ? []:[
                    'name' => $name
                ]);
                $this->typesInstances[$name] = $objectType;
                $types[] = $objectType;
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
        
        $subscription = $this->objectType($schemaSubscription, [
            'name' => 'Subscription'
        ]);
        
        return new Schema([
            'query' => $query,
            'mutation' => !empty($schemaMutation) ? $mutation : null,
            'subscription' => !empty($schemaSubscription) ? $subscription : null,
            'types' => $types
        ]);
    }

    /**
     * @desc   get type from the configuration
     * @author kjone
     * @param $name
     * @param bool $fresh
     * @return ObjectType|mixed|null
     * @throws TypeNotFound
     */
    public function type($name, $fresh = false)
    {
        if (!isset($this->types[$name])) {
            if (!empty(config('graphql.types')[$name])) {
                $this->addType(config('graphql.types')[$name], $name);
            } else {
                throw new TypeNotFound('Type '.$name.' not found.');
            }
        }

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
    
    public function query($query, $variables = [], $opts = [])
    {
        $result = $this->queryAndReturnResult($query, $variables, $opts);
        
        if (!empty($result->errors)) {
            $errorFormatter = config('graphql.error_formatter', [self::class, 'formatError']);
            
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
    
    public function queryAndReturnResult($query, $variables = [], $opts = [])
    {
        $root = array_get($opts, 'root', null);
        $context = array_get($opts, 'context', null);
        $schemaName = array_get($opts, 'schema', null);
        $operationName = array_get($opts, 'operationName', null);
        
        $schema = $this->schema($schemaName);
        
        $result = GraphQLBase::executeAndReturnResult($schema, $query, $root, $context, $variables, $operationName);
        
        return $result;
    }
    
    public function addTypes($types)
    {
        foreach ($types as $name => $type) {
            $this->addType($type, is_numeric($name) ? null:$name);
        }
    }
    
    public function addType($class, $name = null)
    {
        $name = $this->getTypeName($class, $name);
        $this->types[$name] = $class;
        
        event(new TypeAdded($class, $name));
    }
    
    public function addSchema($name, $schema)
    {
        $this->schemas[$name] = $schema;
        
        event(new SchemaAdded($schema, $name));
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

    /**
     * @desc  loaded schemas from inputs
     * @param $inputs
     */
    public function SchemaAutoLoad($inputs)
    {
        $this->clearSchemas();
        $schemaName = config('graphql.schema');
        $Schemas[$schemaName] = config('graphql.schemas.'.$schemaName);
        $action = '';
        $result = [];
        $source = new Source($inputs['query'] ?: '', 'GraphQL request');
        $asta = Parser::parse($source);
        Visitor::visit($asta, [
            'enter' => function(Node $node) use (&$result, $Schemas, &$action, $schemaName) {
                if ($node instanceof OperationDefinitionNode) {
                    $action = $node->operation;
                }
                if ($node instanceof FieldNode) {
                    if (!empty($Schemas[$schemaName][$action][$node->name->value])){
                        $result[$action][$node->name->value] = $Schemas[$schemaName][$action][$node->name->value];
                    }
                    return Visitor::skipNode();
                }
            },
        ]);
        $this->addSchema($schemaName, $result);
    }
}
