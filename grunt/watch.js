module.exports = {
    // configure watch to auto update ------------------------------------------
    grunt: {
        files: [
            'Gruntfile.js'
        ]
    },

    fontcustom: {
        files: [
            '<%= config.src %>/icons/svg/*.svg'
        ],
        tasks: ['webfont', 'sass', 'autoprefixer'],
        options: {
            interrupt: true,
            atBegin: false
        }
    },

    sass: {
        files: [
            '<%= config.src %>/scss/*.scss',
            '<%= config.src %>/scss/**/*.scss'
        ],
        tasks: ['sass', 'cssmin', 'autoprefixer'],
        options: {
            interrupt: true,
            atBegin: true,
            livereload: 232323
        }
    },

    uglify: {
        files: [
            '<%= config.src %>/scripts/*.js'
        ],
        tasks: ['uglify'],
        options: {
            interrupt: true,
            atBegin: true
        }
    }
};
