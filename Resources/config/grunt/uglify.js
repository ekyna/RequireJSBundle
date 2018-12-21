module.exports = function (grunt, options) {
    return {
        require: {
            files: {
                'src/Ekyna/Bundle/RequireJsBundle/Resources/public/plugin/domReady.js': [
                    'node_modules/requirejs-domready/domReady.js'
                ],
                'src/Ekyna/Bundle/RequireJsBundle/Resources/public/plugin/json.js': [
                    'node_modules/requirejs-plugins/src/json.js'
                ],
                'src/Ekyna/Bundle/RequireJsBundle/Resources/public/plugin/text.js': [
                    'node_modules/requirejs-text/text.js'
                ],
                'src/Ekyna/Bundle/RequireJsBundle/Resources/public/require.js': [
                    'node_modules/requirejs/require.js'
                ]
            }
        }
    }
};
