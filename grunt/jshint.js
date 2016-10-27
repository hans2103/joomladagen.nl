module.exports = {
    // configure jshint to validate js files -----------------------------------
    options: {
        reporter: require('jshint-stylish')
    },
    build: ['Gruntfile.js', '<%= config.src %>/js/main.js']
};
