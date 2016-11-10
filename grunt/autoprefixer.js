module.exports = {
    // configure autoprefixer --------------------------------------------------
    options: {
        browsers: ['> 5%', 'last 2 versions', 'ie 11', 'ie 10', 'ie 9']
    },
    files: {
        expand: true,
        flatten: true,
        src: '<%= config.dest %>/css/*.css',
        dest: '<%= config.dest %>/css/'
    }
};




