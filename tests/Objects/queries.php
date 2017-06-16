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

    'examplesWithContext' =>  "
        query QueryExamplesContext {
            examplesContext {
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
            examples {
                test_validation(index: \$index)
            }
        }
    ",

    'updateExampleCustom' =>  "
        mutation UpdateExampleCustom(\$test: String) {
            updateExampleCustom(test: \$test) {
                test
            }
        }
    "

];
