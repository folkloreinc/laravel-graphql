<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\NodeIdField;

class RelayEndpointTest extends RelayTestCase
{
    /**
     * Test schema default
     *
     * @test
     */
    public function testQueryExampleNode()
    {
        $id = 1;
        $query = $this->queries['relayExampleNode'];
        $variables = [
            'id' => $id
        ];
        
        $result = GraphQL::query($query, $variables);
        
        $node = \App\Data::getById($id);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($result['data'], [
            'example' => [
                'id' => NodeIdField::toGlobalId('ExampleNode', $id),
                'name' => $node['name']
            ]
        ]);
    }
    
    /**
     * Test schema default
     *
     * @test
     */
    public function testQueryNode()
    {
        $id = 1;
        $globalId = NodeIdField::toGlobalId('ExampleNode', $id);
        $query = $this->queries['relayNode'];
        $variables = [
            'id' => $globalId
        ];
        
        $result = GraphQL::query($query, $variables);
        
        $node = \App\Data::getById($id);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($result['data'], [
            'node' => [
                'id' => $globalId,
                'name' => $node['name']
            ]
        ]);
    }
    
    /**
     * Test schema default
     *
     * @test
     */
    public function testQueryNodeConnection()
    {
        $id = 2;
        $globalId = NodeIdField::toGlobalId('ExampleNode', $id);
        $query = $this->queries['relayExampleNodeItemsConnection'];
        $variables = [
            'id' => $id
        ];
        
        $result = GraphQL::query($query, $variables);
        
        $node = \App\Data::getById($id);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayNotHasKey('error', $result);
        $items = array_get($result, 'data.example.items', []);
        $edges = array_get($items, 'edges', []);
        $pageInfo = array_get($items, 'pageInfo', []);
        $this->assertEquals(sizeof($edges), sizeof($node['items']));
        $this->assertEquals($pageInfo['startCursor'], $edges[0]['cursor']);
        $this->assertEquals($pageInfo['endCursor'], $edges[sizeof($edges)-1]['cursor']);
        
        $i = 0;
        foreach ($edges as $edge) {
            $this->assertEquals($edge['cursor'], $edge['node']['id']);
            $this->assertEquals($edge['node']['id'], NodeIdField::toGlobalId('ExampleItem', $node['items'][$i]['id']));
            $this->assertEquals($edge['node']['name'], $node['items'][$i]['name']);
            $i++;
        }
    }
    
    /**
     * Test schema default
     *
     * @test
     */
    public function testMutation()
    {
        $id = 1;
        $globalId = NodeIdField::toGlobalId('ExampleNode', $id);
        $clientMutationId = 'TEST_MUTATION_ID';
        $newName = 'New name';
        $query = $this->queries['relayMutation'];
        $variables = [
            'input' => [
                'id' => $globalId,
                'name' => $newName,
                'clientMutationId' => $clientMutationId
            ]
        ];
        
        $result = GraphQL::query($query, $variables);
        
        $this->assertArrayHasKey('data', $result);
        
        $payload = $result['data']['updateName'];
        $this->assertEquals($payload['clientMutationId'], $clientMutationId);
        $this->assertEquals($payload['example']['id'], $globalId);
        $this->assertEquals($payload['example']['name'], $newName);
    }
}
