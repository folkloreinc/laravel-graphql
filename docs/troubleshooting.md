# Troubleshooting errors and problems

This is a collection of common questions and problems.


## Mutation: Cannot read property 'node' of null


**Error:**

If you get this message in your developer console, check the http response under "Network" for this Graphql request.
You might have sent a variable object with one or more values set to '' (empty string) or ' '.
The error message returned by graphql should be something like

    Cannot return null for non-nullable field Type.field_key.
    
**Reason for this error:**

The error occurs, because Laravel >= 5.4 includes a middleware called ``ConvertEmptyStringsToNull.php`` by default, which checks the contents of json objects and converts all empty (and trimmed empty) strings to null.


**Solution:**

To get rid of this error just disable the ``ConvertEmptyStringsToNull`` Middleware by commenting it out in the file ``app/Http/Kernel.php``

