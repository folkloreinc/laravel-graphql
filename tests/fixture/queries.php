<?php


return [

    'examples' =>  "
        query QueryExamples {
            examples {
                id
                name
            }
        }
    ",

    'examplesCustom' =>  "
        query QueryExamplesCustom {
            examplesCustom {
                id
                name
            }
        }
    ",

    'examplesWithVariables' =>  "
        query QueryExamplesVariables(\$id: ID) {
            examples(id: \$id) {
                id
                name
            }
        }
    ",

    'examplesWithContext' =>  "
        query QueryExamplesContext {
            examplesContext {
                id
                name
            }
        }
    ",

    'examplesWithRoot' =>  "
        query QueryExamplesRoot {
            examplesRoot {
                id
                name
            }
        }
    ",

    'examplesWithError' =>  "
        query QueryExamplesWithError {
            examplesQueryNotFound {
                id
                name
            }
        }
    ",

    'examplesWithValidation' =>  "
        query QueryExamplesWithValidation(\$id: ID) {
            examples {
                id
                name_validation(id: \$id)
            }
        }
    ",

    'updateExampleCustom' =>  "
        mutation UpdateExampleCustom(\$name: String) {
            updateExampleCustom(name: \$name) {
                id
                name
            }
        }
    ",

    'relayNode' =>  "
        query QueryRelayNode(\$id: ID!) {
            node(id: \$id) {
                id
                ... on ExampleNode {
                    name
                }
            }
        }
    ",

    'relayExampleNode' =>  "
        query QueryRelayExampleNode(\$id: ID!) {
            example(id: \$id) {
                id
                name
            }
        }
    ",

    'relayExampleNodeItemsConnection' =>  "
        query QueryRelayExampleNodeItemsConnection(\$id: ID!) {
            example(id: \$id) {
                id
                items {
                    edges {
                        cursor
                        node {
                            id
                            name
                        }
                    }
                    pageInfo {
                        hasNextPage
                        hasPreviousPage
                        startCursor
                        endCursor
                    }
                }
            }
        }
    ",

    'relayMutation' =>  "
        mutation UpdateName(\$input: UpdateNameInput) {
            updateName(input: \$input) {
                example {
                    id
                    name
                }
                clientMutationId
            }
        }
    "

];
