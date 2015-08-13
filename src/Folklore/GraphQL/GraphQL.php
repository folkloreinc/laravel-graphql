<?php namespace Folklore\GraphQL;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Schema;

class GraphQL {
    
    public function schema()
    {
        $schema = config('graphql.schema');
        if($schema instanceof Schema)
        {
            return $schema;
        }
        
        $queryType = array_get($schema, 'query', null);
        $mutationType = array_get($schema, 'mutation', null);
        
        return new Schema($queryType, $mutationType);
    }
    
    public function query($query, $params = [])
    {
        $schema = $this->schema();
        return GraphQLBase::execute($schema, $query, null, $params);
    }
}
