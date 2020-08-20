module.exports = function(grunt) {
    grunt.initConfig({
        makepot: {
            target: {
                options: {
                    domainPath: '/languages',
                    mainFile: 'omise-woocommerce.php',
                    potComments: 'This file is distributed under the same license as the Omise package.',
                    potFilename: 'omise.pot',
                    potHeaders: {
                        poedit: false,                  // Includes common Poedit headers.
                        'x-poedit-keywordslist': true,  // Include a list of all possible gettext functions.
                        'Project-Id-Version': 'Omise Payment Gateway v4.1',
                        'Report-Msgid-Bugs-To': 'https://github.com/omise/omise-woocommerce/issues'
                    },                                  // Headers to add to the generated POT file.
                    processPot: null,                   // A callback function for manipulating the POT file.
                    type: 'wp-plugin',                  // Type of project (wp-plugin or wp-theme).
                    updateTimestamp: true,              // Whether the POT-Creation-Date should be updated without other changes.
                    updatePoFiles: false                // Whether to update PO files in the same directory as the POT file.
                }
            }
        }
    });

    grunt.loadNpmTasks( 'grunt-wp-i18n' );
};
