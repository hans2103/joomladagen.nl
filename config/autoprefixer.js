'use strict';

// Add vendor prefixed styles
module.exports = {
    options: {
        browsers: ['> 5%', 'last 2 versions', 'ie 11']
    },
    files: {
        expand: true,
        flatten: true,
        src: '<%= paths.template %>/css/*.css',
        dest: '<%= paths.template %>/css/'
    }
};
