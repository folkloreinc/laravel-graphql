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
    
    'examplesWithParams' =>  "
        query QueryExamplesParams(\$index: Int) {
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
        query QueryExamplesWithValidation {
            examples {
                test_validation
            }
        }
    "
    
];
