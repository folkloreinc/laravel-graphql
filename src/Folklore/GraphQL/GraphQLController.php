<?php namespace Folklore\GraphQL;

use Illuminate\Http\Request;
use Auth;

class GraphQLController extends Controller
{
    public function query(Request $request, $schema = null)
    {
        if (!$schema) {
            $schema = config('graphql.schema');
        }
        
        $variableInputName = config('graphql.variables_input_name', 'params');
        $query = $request->get('query');
        $params = $request->get($variableInputName);
        $operationName = $request->get('operationName', null);
        
        if (is_string($params)) {
            $params = json_decode($params, true);
        }
        
        $context = $this->queryContext($query, $params, $schema);
        
        return app('graphql')->query($query, $params, [
            'context' => $context,
            'schema' => $schema,
            'operationName' => $operationName
        ]);
    }
    
    protected function queryContext($query, $params, $schema)
    {
        return Auth::user();
    }
}
