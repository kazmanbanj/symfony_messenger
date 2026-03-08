const Encore = require('@symfony/webpack-encore');

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')

    /*
     * ENTRY CONFIG
     */
    .addEntry('app', './assets/js/app.js')

    .splitEntryChunks()
    .enableSingleRuntimeChunk()

    /*
     * VUE 3 SUPPORT
     */
    .enableVueLoader(() => {}, { version: 3 })

    /*
     * FEATURE CONFIG
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .configureBabel(() => {}, {
        useBuiltIns: 'usage',
        corejs: 3
    })

    .enableSassLoader()
;

module.exports = Encore.getWebpackConfig();
