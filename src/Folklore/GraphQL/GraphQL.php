<?php namespace Folklore\GraphQL;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Schema;

use GraphQL\Type\Definition\ObjectType;

class GraphQL {
    
    protected $mutations = [];
    protected $queries = [];

    public function schema()
    {
        $schema = config('graphql.schema');
        if($schema instanceof Schema)
        {
            return $schema;
        }
        
        $queries = array_merge(array_get($schema, 'queries', []), $this->queries);
        $mutations = array_merge(array_get($schema, 'mutations', []), $this->mutations);
        
        $queryFields = [];
        foreach($queries as $key => $query)
        {
            $queryFields[$key] = is_array($query) ? $query:$query->toArray();
        }
        
        $mutationFields = [];
        foreach($mutations as $key => $mutation)
        {
            $mutationFields[$key] = is_array($mutation) ? $mutation:$mutation->toArray();
        }
        
        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => $queryFields
        ]);
        
        $mutationType = new ObjectType([
            'name' => 'Mutation',
            'fields' => $mutationFields
        ]);
        
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
}
