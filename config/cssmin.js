'use strict';

//
module.exports = {
    options: {
        roundingPrecision: -1,
        sourceMap: false
    },
    site: {
        files: {
            '<%= paths.template %>/css/style.css': '<%= paths.template %>/css/style.css',
            '<%= paths.template %>/css/font.css': '<%= paths.template %>/css/font.css'
        }
    }
};
