'use strict';

// Autoprefixer
module.exports = {
    options: {
        browsers: ['> 5%', 'last 2 versions']
    },
    files: {
        expand: true,
        flatten: true,
        src: '<%= paths.template %>/css/*.css',
        dest: '<%= paths.template %>/css/'
    }
};
