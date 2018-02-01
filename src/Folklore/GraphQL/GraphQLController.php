<?php namespace Folklore\GraphQL;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GraphQLController extends Controller
{
    public function __construct(Request $request)
    {
        $route = $request->route();

        /**
         * Prevent schema middlewares to be applied to graphiql routes
         *
         * Be careful !! For Lumen < 5.6, Request->route() returns an array with
         * 'as' key for named routes
         *
         * @see https://github.com/laravel/lumen-framework/issues/119
         * @see https://laravel.com/api/5.5/Illuminate/Http/Request.html#method_route
         */
        
        $prefix = config('graphql.prefix');
        
        $routeName = is_object($route)
            ? $route->getName()
            : (is_array($route) && isset($route['as'])
                ? $route['as']
                : null);

        if (!is_null($routeName) && preg_match('/^graphql\.graphiql/', $routeName)) {
            return;
        }

        $defaultSchema = config('graphql.schema');
        if (is_array($route)) {
            $schema = array_get($route, '2.'.$prefix.'_schema', $defaultSchema);
        } elseif (is_object($route)) {
            $schema = $route->parameter($prefix.'_schema', $defaultSchema);
        } else {
            $schema = $defaultSchema;
        }

        $middleware = config('graphql.middleware_schema.' . $schema, null);

        if ($middleware) {
            $this->middleware($middleware);
        }
    }

    public function query(Request $request, $graphql_schema = null)
    {
        $isBatch = !$request->has('query');
        $inputs = $request->all();

        if (is_null($graphql_schema)) {
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
            'graphql_schema' => $graphql_schema,
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
