module.exports = {
    // compile less stylesheets to css -----------------------------------------
    options: {
        outputStyle: 'compact',
        sourceMap: true
    },
    build: {
        files: {
            '<%= config.dest %>/css/master.css': '<%= config.src %>/less/master.less'
        }
    }
};
