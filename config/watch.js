'use strict';

// Watches files for changes and runs tasks based on the changed files
module.exports = {
    gruntfile: {
        files: ['Gruntfile.js']
    },
    sass: {
        files: [
            '<%= paths.assets %>/scss/*.scss',
            '<%= paths.assets %>/scss/**/*.scss'
        ],
        tasks: ['sass', 'cssmin', 'autoprefixer'],
        options: {
            interrupt: true,
            atBegin: true
        }
    },
    uglify: {
        files: ['<%= paths.assets %>/scripts/*.js'],
        tasks: ['uglify'],
        options: {
            interrupt: true,
            atBegin: true
        }
    },
    concat: {
        files: [
            '<%= paths.assets %>/scripts/*.js'
        ],
        tasks: ['concat'],
        options: {
            interrupt: true,
            atBegin: true
        }
    },
    svg: {
        files: [
            '<%= paths.assets %>/icons/svg/*.svg'
        ],
        tasks: ['svgstore'],
        options: {
            interrupt: true,
            atBegin: true
        }
    }
};
