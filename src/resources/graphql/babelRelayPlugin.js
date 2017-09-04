// `babel-relay-plugin` returns a function for creating plugin instances
var getBabelRelayPlugin = require('babel-relay-plugin');

// Load the schema
var schemaData = require('./schema.json');

// create a plugin instance with the schema
module.exports = getBabelRelayPlugin(schemaData.data || schemaData, {
    // Only if `enforceSchema` is `false` and `debug` is `true`
    // will validation errors be logged at build time.
    debug: true,
    // Suppresses all warnings that would be printed.
    suppressWarnings: false
});
