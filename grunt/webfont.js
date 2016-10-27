module.exports = {
    // Iconfont ----------------------------------------------------------------
    icons: {
        src: '<%= config.src %>/icons/svg/*.svg',
        dest: '<%= config.dest %>/fonts/icons',
        destCss: '<%= config.dest %>/scss/utilities',
        options: {
            font: 'icons',
            hashes: false,
            stylesheet: 'scss',
            relativeFontPath: '../fonts/icons/',
            template: '<%= config.dest %>/icons/template.css',
            htmlDemo: false
        }
    }
};
