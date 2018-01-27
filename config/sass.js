'use strict';

// SASS
module.exports = {
    site: {
        options: {
            includePaths: [
                'node_modules'
            ],
            outputStyle: 'expanded',
            sourceMap: false
        },
        files: {
            '<%= paths.template %>/css/style.css': '<%= paths.assets %>/scss/style.scss',
            '<%= paths.template %>/css/grid.css': '<%= paths.assets %>/scss/grid.scss',
            '<%= paths.template %>/css/font.css': '<%= paths.assets %>/scss/font.scss'
        }
    }
};
