<?php

namespace Folklore\GraphQL\Relay;

use Folklore\GraphQL\Relay\Support\ConnectionField;
use Folklore\GraphQL\Relay\Support\ConnectionType;
use GraphQL;

class Relay
{
    protected $app;
    protected $graphql;

    public function __construct($app)
    {
        $this->app = $app;
        $this->graphql = $app['graphql'];
    }

    public function connectionField($config = [])
    {
        $field = new ConnectionField($config);
        return $field;
    }

    public function connectionFieldFromEdgeType($edgeType, $config = [])
    {
        $typeName = array_get($edgeType->config, 'name');
        $connectionName = array_get($config, 'connectionTypeName', str_plural($typeName).'Connection');

        $connectionType = new ConnectionType([
            'name' => $connectionName
        ]);
        $connectionType->setEdgeType($edgeType);
        GraphQL::addType($connectionType, $connectionName);

        $fieldConfig = array_except($config, ['connectionTypeName']);
        $field = new ConnectionField($fieldConfig);
        $field->setType(GraphQL::type($connectionName));
        return $field;
    }

    public function connectionFieldFromEdgeTypeAndQueryBuilder($edgeType, $queryBuilderResolver, $config = [])
    {
        $field = $this->connectionFieldFromEdgeType($edgeType, $config);
        $field->setQueryBuilderResolver($queryBuilderResolver);
        return $field;
    }

    public function toGlobalId($type, $id)
    {
        return base64_encode($type.':'.$id);
    }

    public function fromGlobalId($globalId)
    {
        $id = explode(':', base64_decode($globalId), 2);
        return sizeof($id) === 2 ? [
            'type' => $id[0],
            'id' => $id[1]
        ]:null;
    }

    public function getIdFromGlobalId($globalId)
    {
        $id = $this->fromGlobalId($globalId);
        return $id ? $id['id']:null;
    }

    public function getTypeFromGlobalId($globalId)
    {
        $id = $this->fromGlobalId($globalId);
        return $id ? $id['type']:null;
    }
}
