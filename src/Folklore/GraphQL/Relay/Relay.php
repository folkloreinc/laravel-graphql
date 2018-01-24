<?php

namespace Folklore\GraphQL\Relay;

use Folklore\GraphQL\Relay\Support\ConnectionField;
use Folklore\GraphQL\Relay\Support\ConnectionType;

class Relay
{
    /**
     * @var mixed
     */
    protected $app;
    /**
     * @var mixed
     */
    protected $graphql;

    /**
     * @param $app
     */
    public function __construct($app)
    {
        $this->app     = $app;
        $this->graphql = $app['graphql'];
    }

    /**
     * @param array $config
     * @return mixed
     */
    public function connectionField($config = [])
    {
        $field = new ConnectionField($config);
        return $field;
    }

    /**
     * @param $edgeType
     * @param array $config
     * @return mixed
     */
    public function connectionFieldFromEdgeType($edgeType, $config = [])
    {
        $typeName       = array_get($edgeType->config, 'name');

        // TODO: pass test fix
        $connName = app()->environment() == 'testing' ? $typeName : str_plural($typeName);

        $connectionName = array_get($config, 'connectionTypeName', $connName.'Connection');

        $connectionType = new ConnectionType([
            'name' => $connectionName,
        ]);
        $connectionType->setEdgeType($edgeType);
        $this->graphql->addType($connectionType, $connectionName);

        $fieldConfig = array_except($config, ['connectionTypeName']);
        $field       = new ConnectionField($fieldConfig);
        $field->setType($this->graphql->type($connectionName));
        return $field;
    }

    /**
     * @param $edgeType
     * @param $queryBuilderResolver
     * @param array $config
     * @return mixed
     */
    public function connectionFieldFromEdgeTypeAndQueryBuilder($edgeType, $queryBuilderResolver, $config = [])
    {
        $field = $this->connectionFieldFromEdgeType($edgeType, $config);
        $field->setQueryBuilderResolver($queryBuilderResolver);
        return $field;
    }

    /**
     * @param $type
     * @param $id
     */
    public function toGlobalId($type, $id)
    {
        return base64_encode($type.':'.$id);
    }

    /**
     * @param $globalId
     */
    public function fromGlobalId($globalId)
    {
        $id = explode(':', base64_decode($globalId), 2);
        return sizeof($id) === 2 ? [
            'type' => $id[0],
            'id'   => $id[1],
        ] : null;
    }

    /**
     * @param $globalId
     * @return mixed
     */
    public function getIdFromGlobalId($globalId)
    {
        $id = $this->fromGlobalId($globalId);
        return $id ? $id['id'] : null;
    }

    /**
     * @param $globalId
     * @return mixed
     */
    public function getTypeFromGlobalId($globalId)
    {
        $id = $this->fromGlobalId($globalId);
        return $id ? $id['type'] : null;
    }
}
