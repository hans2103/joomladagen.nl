'use strict';

//
module.exports = {
    options: {
        roundingPrecision: -1,
        sourceMap: true
    },
    site: {
        files: {
            '<%= paths.template %>/css/style.css': '<%= paths.template %>/css/style.css',
            '<%= paths.template %>/css/font.css': '<%= paths.template %>/css/font.css'
        }
    }
};
