'use strict';

//
module.exports = {
    options: {
        sourceMap: false
    },
    build: {
        files: {
            '<%= paths.template %>/js/scripts.js': '<%= templateScripts %>'
        }
    }
};
