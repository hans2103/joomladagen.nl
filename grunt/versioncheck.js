module.exports = {
    // Checks if your NPM or Bower dependencies are out of date --------------------
    target: {
        options: {
            skip : ["semver", "npm", "lodash"],
            hideUpToDate : false
        }
    }
};
