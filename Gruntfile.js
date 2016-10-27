module.exports = function (grunt) {

    // measures the time each task takes
    require('time-grunt')(grunt);

    var config = {
        src: 'public_html/templates/shaper_helix3',
        dest: 'public_html/templates/shaper_helix3',
        proxy: 'http://joomladagen.local'
    };

    // load grunt config
    require('load-grunt-config')(grunt, {
        config: {
            config: config
        }
    });

    // grunt tasks
    grunt.registerTask('default', ['versioncheck', 'jshint', 'uglify', 'cssmin', 'less', 'watch']);

    grunt.registerTask('local', ['jshint', 'uglify', 'cssmin', 'less', 'watch']);

};
