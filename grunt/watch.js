module.exports = {
    // configure watch to auto update ------------------------------------------
    less: {
        files: [
            '<%= config.src %>/less/*.less'
        ],
        tasks: ['less', 'cssmin'],
        options: {
            livereload: true,
            interrupt: true,
            atBegin: true
        }
    },

    scripts: {
        files: '<%= config.src %>/js/*.js',
        tasks: ['jshint', 'uglify'],
        options: {
            livereload: true,
            interrupt: true,
            atBegin: true
        }
    }
};
