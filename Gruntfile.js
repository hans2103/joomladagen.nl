module.exports = function (grunt) {

    // measures the time each task takes
    require('time-grunt')(grunt);

    var config = {
        src: 'public_html/templates/perfecttemplate',
        dest: 'public_html/templates/perfecttemplate',
        proxy: 'http://joomladagen.local',
        templateScripts: [
            'bower_components/apollo.js/dist/apollo.min.js',
            'bower_components/vanilla-js-responsive-menu/vanilla.js.responsive.menu.js',
            //'bower_components/jquery/dist/jquery.min.js',
            '<%= config.src %>/scripts/svg-injector.js',
            '<%= config.src %>/scripts/main.js'
        ]
    };

    // load grunt config
    require('load-grunt-config')(grunt, {
        config: {
            config: config
        }
    });

    // grunt tasks
    grunt.registerTask('default', ['shell', 'modernizr', 'webfont', 'watch']);

};
