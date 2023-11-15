var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('./src/Resources/public/')
    .setPublicPath('/bundles/cooptilleulssyliusclickncollectplugin/')
    .setManifestKeyPrefix('')

    .cleanupOutputBeforeBuild()
    .disableSingleRuntimeChunk()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .configureCssMinimizerPlugin()

    .addEntry('click-n-collect', './assets/js/click_n_collect.js')
    .addEntry('admin-locations', './assets/js/admin/locations.js')
    .addEntry('admin-collections', './assets/js/admin/collections.js')
;

module.exports = Encore.getWebpackConfig();
