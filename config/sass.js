'use strict';

//
module.exports = {
    dist: {
        options: {
            includePaths: [
                require("node-normalize-scss").includePaths
            ],
            sourceMap: false
        },
        files: {
            '<%= paths.template %>/css/style.css': '<%= paths.assets %>/scss/style.scss',
            '<%= paths.template %>/css/grid.css': '<%= paths.assets %>/scss/grid.scss',
            '<%= paths.template %>/css/font.css': '<%= paths.assets %>/scss/font.scss'
        }
    }
};
