const Encore = require('@symfony/webpack-encore');

Encore
  .setOutputPath('src/Resources/public')
  .setPublicPath('/bundles/nucleosantispam')
  .setManifestKeyPrefix('')
  .cleanupOutputBeforeBuild()
  .disableSingleRuntimeChunk()
  .enableSourceMaps(!Encore.isProduction())
  .addEntry('widget', './assets/widget.js')
;

module.exports = Encore.getWebpackConfig();
