<?php


return [

    'examples' =>  "
        query QueryExamples {
            examples {
                test
            }
        }
    ",

    'examplesCustom' =>  "
        query QueryExamplesCustom {
            examplesCustom {
                test
            }
        }
    ",

    'examplesWithVariables' =>  "
        query QueryExamplesVariables(\$index: Int) {
            examples(index: \$index) {
                test
            }
        }
    ",

    'shorthandExamplesWithVariables' =>  "
        query QueryShorthandExamplesVariables(\$message: String!) {
            echo(message: \$message)
        }
    ",

    'examplesWithContext' =>  "
        query QueryExamplesContext {
            examplesContext {
                test
            }
        }
    ",

    'examplesWithAuthorize' =>  "
        query QueryExamplesAuthorize {
            examplesAuthorize {
                test
            }
        }
    ",

    'examplesWithAuthenticated' =>  "
        query QueryExamplesAuthenticated {
            examplesAuthenticated {
                test
            }
        }
    ",

    'examplesWithRoot' =>  "
        query QueryExamplesRoot {
            examplesRoot {
                test
            }
        }
    ",

    'examplesWithError' =>  "
        query QueryExamplesWithError {
            examplesQueryNotFound {
                test
            }
        }
    ",

    'examplesWithValidation' =>  "
        query QueryExamplesWithValidation(\$index: Int) {
            examples(index: \$index) {
                test
            }
        }
    ",

    'exampleMutation' => "
        mutation ExampleMutation(\$required: String) {
            exampleMutation(required: \$required) {
                test
            }
        }
    ",

    'examplePagination' => "
        query Items(\$take: Int!, \$page: Int!) {
            examplesPagination(take: \$take, page: \$page) {
                items {
                    test
                }
                cursor {
                    total
                    perPage
                    currentPage
                    hasPages
                }
            }
        }
    ",

];
