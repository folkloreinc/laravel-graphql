<?php namespace Folklore\GraphQL;

use Illuminate\Http\Request;

class GraphQLController extends Controller
{
    public function query(Request $request, $schema = null)
    {
        $isBatch = false;
        $requestVariables = $request->all();
        if(isset($requestVariables[0])) {
            $isBatch = true;
        }
        if (!$schema) {
            $schema = config('graphql.schema');
        }
        $variableInputName = config('graphql.variables_input_name', 'params');

        if (!$isBatch) {
            return $this->execQuery($schema, $variableInputName, $requestVariables);
        }

        $results = [];
        foreach ($requestVariables as $data) {
            $results[] = $this->execQuery($schema, $variableInputName, $data);
        }
        return $results;
    }

    public function execQuery($schema, $variableInputName, $data) {
        $query = $data['query'];
        $params = isset($data[$variableInputName]) ? $data[$variableInputName] : null;
        if (is_string($params)) {
            $params = json_decode($params, true);
        }
        $operationName = isset($data['operationName']) ? $data['operationName'] : null;
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
            return app('auth')->user();
        } catch (\Exception $e) {
            return null;
        }
    }
}
