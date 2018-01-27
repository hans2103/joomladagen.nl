'use strict';

module.exports = function(grunt) {

    // measures the time each task takes
    require('time-grunt')(grunt);

    // load time-grunt and all grunt plugins found in the package.json
    require('jit-grunt')(grunt);

    var options = {
        // Project settings
        paths: {
            // Configurable paths
            template: 'public_html/templates/jd18nl',
            assets: 'public_html/templates/jd18nl/assets'
        },
        browsersync : {
            port : '5666', //NVML
            proxy: 'joomladagen.test',
            open: false
        }
    };

    // Load grunt configurations automatically
    var configs = require('load-grunt-configs')(grunt, options);

    // grunt config
    grunt.initConfig(configs);

    // The dev task will be used during development
    grunt.registerTask('default', ['modernizr', 'browserSync', 'watch']);

    // JS only
    grunt.registerTask('js', ['watch:concat']);

    //
    // // The dev task will be used during development
    // grunt.registerTask('default', [
    //     'shell',
    //     'copy',
    //     'modernizr',
    //     'browserSync',
    //     'watch'
    // ]);
    //
    // // The js task will be used during development
    // grunt.registerTask('dev', [
    //     'watch:concat'
    // ]);

};
