// `babel-relay-plugin` returns a function for creating plugin instances
var getBabelRelayPlugin = require('babel-relay-plugin');

// load previously saved schema data (see "Schema JSON" below)
var schemaData = require('./schema.json');

// create a plugin instance
var relayPlugin = getBabelRelayPlugin(schemaData, {
    // Only if `enforceSchema` is `false` and `debug` is `true`
    // will validation errors be logged at build time.
    debug: true,
    // Suppresses all warnings that would be printed.
    suppressWarnings: false
});

return babel.transform(source, {
  plugins: [
    [
        relayPlugin,
        {
            // Will throw an error when it validates the queries at build time.
            enforceSchema: true
        }
    ]
  ]
});
