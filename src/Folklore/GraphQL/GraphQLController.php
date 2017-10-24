<?php namespace Folklore\GraphQL;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GraphQLController extends Controller
{
    public function query(Request $request, $graphql_schema = null)
    {
        $isBatch = !$request->has('query');
        $inputs = $request->all();

        if (!$graphql_schema) {
            $graphql_schema = config('graphql.schema');
        }

        if (!$isBatch) {
            $data = $this->executeQuery($graphql_schema, $inputs);
        } else {
            $data = [];
            foreach ($inputs as $input) {
                $data[] = $this->executeQuery($graphql_schema, $input);
            }
        }

        $headers = config('graphql.headers', []);
        $options = config('graphql.json_encoding_options', 0);

        $errors = !$isBatch ? array_get($data, 'errors', []) : [];
        $authorized = array_reduce($errors, function ($authorized, $error) {
            return !$authorized || array_get($error, 'message') === 'Unauthorized' ? false : true;
        }, true);
        if (!$authorized) {
            return response()->json($data, 403, $headers, $options);
        }

        return response()->json($data, 200, $headers, $options);
    }

    public function graphiql(Request $request, $graphql_schema = null)
    {
        $view = config('graphql.graphiql.view', 'graphql::graphiql');
        return view($view, [
            'schema' => $graphql_schema,
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
