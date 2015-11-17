<?php namespace Folklore\GraphQL;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class GraphQLController extends Controller {

    public function query(Request $request)
    {
        $query = $request->get('query');
        $params = $request->get('variables');

        if(is_string($params))
        {
            $params = json_decode($params, true);
        }

        return app('graphql')->query($query, $params);
    }

}
