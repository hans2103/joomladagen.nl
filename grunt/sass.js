module.exports = {
    // compile sass stylesheets to css -----------------------------------------
    build: {
        files: {
            '<%= config.dest %>/css/style.css': '<%= config.src %>/scss/style.scss',
            '<%= config.dest %>/css/font.css': '<%= config.src %>/scss/font.scss'
        }
    }
};
