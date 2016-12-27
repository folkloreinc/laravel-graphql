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
        
        // Backward compatibility
        $oldVariablesInputName = config('graphql.variables_input_name', 'params');
        $variablesInputName = config('graphql.request_variables_name', $oldVariablesInputName);
        
        $query = $request->get('query');
        $params = $request->get($variablesInputName);
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
        try {
            return Auth::user();
        } catch (\Exception $e) {
            return null;
        }
    }
}
