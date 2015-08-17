<?php namespace Folklore\GraphQL;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Schema;

use GraphQL\Type\Definition\ObjectType;

class GraphQL {
    
    protected $app;
    
    protected $mutations = [];
    protected $queries = [];
    protected $types = [];
    
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function schema()
    {
        $schema = config('graphql.schema');
        if($schema instanceof Schema)
        {
            return $schema;
        }
        
        $configQuery = array_get($schema, 'query', []);
        $configMutation = array_get($schema, 'mutation', []);
        
        if(is_string($configQuery))
        {
            $queryType = $this->app->make($configQuery)->toType();
        }
        else
        {
            $queryFields = array_merge($configQuery, $this->queries);
            
            $queryType = $this->buildTypeFromFields($queryFields, [
                'name' => 'Query'
            ]);
        }
        
        if(is_string($configMutation))
        {
            $mutationType = $this->app->make($configMutation)->toType();
        }
        else
        {
            $mutationFields = array_merge($configMutation, $this->mutations);
            
            $mutationType = $this->buildTypeFromFields($mutationFields, [
                'name' => 'Mutation'
            ]);
        }
        
        return new Schema($queryType, $mutationType);
    }
    
    protected function buildTypeFromFields($fields, $opts = [])
    {
        $typeFields = [];
        foreach($fields as $key => $field)
        {
            if(is_string($field))
            {
                $typeFields[$key] = app($field)->toArray();
            }
            else
            {
                $typeFields[$key] = $field;
            }
        }
        
        return new ObjectType(array_merge([
            'fields' => $typeFields
        ], $opts));
    }
    
    public function query($query, $params = [])
    {
        $schema = $this->schema();
        return GraphQLBase::execute($schema, $query, null, $params);
    }
    
    public function addMutation($name, $mutator)
    {
        $this->mutations[$name] = $mutator;
    }
    
    public function addQuery($name, $query)
    {
        $this->queries[$name] = $query;
    }
    
    public function addType($name, $type)
    {
        $this->types[$name] = $type;
    }
    
    public function type($name)
    {
        $configTypes = config('graphql.types');
        $types = array_merge($configTypes, $this->types);
        
        if(!isset($types[$name]))
        {
            throw new \Exception('Type '.$name.' not found.');
        }
        
        $type = app($types[$name]);
        
        return $type->toType();
    }
}
