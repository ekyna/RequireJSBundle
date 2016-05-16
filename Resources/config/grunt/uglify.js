module.exports = function (grunt, options) {
    return {
        require: {
            files: {
                'src/Ekyna/Bundle/RequireJsBundle/Resources/public/plugin/domReady.js': [
                    'bower_components/domReady/domReady.js'
                ],
                'src/Ekyna/Bundle/RequireJsBundle/Resources/public/plugin/json.js': [
                    'bower_components/requirejs-plugins/src/json.js'
                ],
                'src/Ekyna/Bundle/RequireJsBundle/Resources/public/plugin/text.js': [
                    'bower_components/text/text.js'
                ],
                'src/Ekyna/Bundle/RequireJsBundle/Resources/public/require.js': [
                    'node_modules/requirejs/require.js'
                ],
                'src/Ekyna/Bundle/RequireJsBundle/Resources/public/r.js': [
                    'node_modules/requirejs/bin/r.js'
                ]
            }
        }
    }
};
