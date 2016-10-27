module.exports = {
    // configure cssmin to minify css files ------------------------------------
    options: {
        banner: '/*\n <%= package.name %> <%= grunt.template.today("yyyy-mm-dd") %> \n*/\n',
        roundingPrecision: -1,
        sourceMap: true
    },
    build: {
        files: {
            '<%= config.dest %>/css/template.css': '<%= config.src %>/css/master.css'
        }
    }
};
