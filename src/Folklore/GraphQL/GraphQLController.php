<?php namespace Folklore\GraphQL;

use Illuminate\Http\Request;

class GraphQLController extends Controller
{
    public function query(Request $request, $schema = null)
    {
        $inputs = $request->all();
        $isBatch = array_keys($inputs) === range(0, count($inputs) - 1);
        //production loaded schemas from inputs
        if (app()->environment('production')) {
            \GraphQL::SchemaAutoLoad($inputs);
        }
        if (!$schema) {
            $schema = config('graphql.schema');
        }

        if (!$isBatch) {
            $data = $this->executeQuery($schema, $inputs);
        } else {
            $data = [];
            foreach ($inputs as $input) {
                $data[] = $this->executeQuery($schema, $input);
            }
        }

        $headers = config('graphql.headers', []);
        $options = config('graphql.json_encoding_options', 0);
        return response()->json($data, 200, $headers, $options);
    }

    public function graphiql(Request $request, $schema = null)
    {
        $view = config('graphql.graphiql.view', 'graphql::graphiql');
        return view($view, [
            'schema' => $schema,
        ]);
    }

    protected function executeQuery($schema, $input)
    {
        $variablesInputName = config('graphql.variables_input_name', 'variables');
        $query = array_get($input, 'query');
        $variables = array_get($input, $variablesInputName);
        if (is_string($variables)) {
            $variables = json_decode($variables, true);
        }
        $operationName = array_get($input, 'operationName');
        $context = $this->queryContext($query, $variables, $schema);
        return app('graphql')->query($query, $variables, [
            'context' => $context,
            'schema' => $schema,
            'operationName' => $operationName
        ]);
    }

    protected function queryContext($query, $variables, $schema)
    {
        try {
            return app('auth')->user();
        } catch (\Exception $e) {
            return null;
        }
    }
}
