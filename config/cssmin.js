'use strict';

// CSS Minify and clean
module.exports = {
    options: {
        roundingPrecision: -1,
        sourceMap: false
    },
    dist: {
        files: [{
            expand: true,
            cwd: '<%= paths.template %>/css',
            src: ['*.css', '!*.min.css'],
            dest: '<%= paths.template %>/css',
            ext: '.min.css'
        }]
    }
};
