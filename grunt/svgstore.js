module.exports = {
    // configure svgstore ------------------------------------------------------
    build: {
        files: {
            '<%= config.src %>/icons/icons.svg': [
                '<%= config.src %>/icons/svg/*.svg'
            ]
        }
    }
};
