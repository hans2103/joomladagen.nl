module.exports = {
    // configure concat --------------------------------------------------------
    options: {
        separator: '\r\n'
    },
    default: {
        src: '<%= config.templateScripts %>',
        dest: '<%= config.dest %>/js/scripts.js'
    }
};
