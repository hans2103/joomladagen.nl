module.exports = {
    // configure uglify to minify js files -------------------------------------
    options: {
        banner: '/*\n <%= package.name %> <%= grunt.template.today("yyyy-mm-dd") %> \n*/\n'
    },
    build: {
        files: {
            '<%= config.dest %>/js/main.min.js': '<%= config.src %>/js/main.js'
        }
    }
};
