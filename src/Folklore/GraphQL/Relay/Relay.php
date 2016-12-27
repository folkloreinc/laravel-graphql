<?php

namespace Folklore\GraphQL\Relay;

class Relay
{
    protected $app;
    protected $graphql;
    
    public function __construct($app)
    {
        $this->app = $app;
        $this->graphql = $app['graphql'];
    }
    
    public function toGlobalId($type, $id)
    {
        return base64_encode($type.':'.$id);
    }
    
    public function fromGlobalId($globalId)
    {
        return explode(':', base64_decode($globalId));
    }
    
    public function getIdFromGlobalId($globalId)
    {
        list($type, $id) = $this->fromGlobalId($globalId);
        return $id;
    }
    
    public function getTypeFromGlobalId($globalId)
    {
        list($type, $id) = $this->fromGlobalId($globalId);
        return $type;
    }
}
