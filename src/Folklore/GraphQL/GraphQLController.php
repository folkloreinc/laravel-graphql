<?php namespace Folklore\GraphQL;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Auth;

class GraphQLController extends Controller
{
    
    public function query(Request $request, $schema = null)
    {
        if (!$schema) {
            $schema = config('graphql.schema');
        }
        
        $query = $request->get('query');
        $params = $request->get('params');
        
        if (is_string($params)) {
            $params = json_decode($params, true);
        }
        
        $context = $this->queryContext($query, $params, $schema);
        
        return app('graphql')->query($query, $params, [
            'context' => $context,
            'schema' => $schema
        ]);
    }
    
    protected function queryContext($query, $params, $schema)
    {
        return Auth::user();
    }
}
