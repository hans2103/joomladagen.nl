'use strict';

//
module.exports = {
    options: {
        separator: '\r\n'
    },

    // Default
    default: {
        files: [
            {
                expand: true,
                src: [
                    '<%= paths.assets %>/icons/svg/*.svg'
                ],
                dest: '<%= paths.template %>/icons',
                flatten: true
            }
        ]
    }
};
