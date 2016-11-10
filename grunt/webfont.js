module.exports = {
    // Iconfont ----------------------------------------------------------------
    icons: {
        src: '<%= config.src %>/icons/svg/*.svg',
        dest: '<%= config.dest %>/fonts/icons',
        destCss: '<%= config.src %>/scss/utilities',
        options: {
            font: 'icons',
            hashes: false,
            stylesheet: 'scss',
            relativeFontPath: '../fonts/icons/',
            template: '<%= config.src %>/icons/template.css',
            htmlDemo: false
        }
    }
};
