<?php namespace Folklore\GraphQL;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Schema;

use GraphQL\Type\Definition\ObjectType;

class GraphQL {
    
    protected $mutations = [];
    protected $queries = [];
    protected $types = [];

    public function schema()
    {
        $schema = config('graphql.schema');
        if($schema instanceof Schema)
        {
            return $schema;
        }
        
        $configQuery = array_get($schema, 'query', []);
        $configMutation = array_get($schema, 'mutation', []);
        
        if(is_string($configQuery) && $this->app->bound($configQuery))
        {
            $queryType = $this->app->make($configQuery)->toType();
        }
        else
        {
            $queries = array_merge($configQuery, $this->queries);
            $queryFields = [];
            foreach($queries as $key => $query)
            {
                $queryFields[$key] = is_array($query) ? $query:$query->toArray();
            }
            $queryType = new ObjectType([
                'name' => 'Query',
                'fields' => $queryFields
            ]);
        }
        
        if(is_string($configMutation) && $this->app->bound($configMutation))
        {
            $mutationType = $this->app->make($configMutation)->toType();
        }
        else
        {
            $mutations = array_merge(array_get($schema, 'mutations', []), $this->mutations);
            
            $mutationFields = [];
            foreach($mutations as $key => $mutation)
            {
                $mutationFields[$key] = is_array($mutation) ? $mutation:$mutation->toArray();
            }
            
            $mutationType = new ObjectType([
                'name' => 'Mutation',
                'fields' => $mutationFields
            ]);
        }
        
        return new Schema($queryType, $mutationType);
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
        
        return $this->toType();
    }
}
