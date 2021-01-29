const { merge } = require('webpack-merge');
const path = require('path');
const webpackConfig = require('@nextcloud/webpack-vue-config');

const config = {

};

const mergedConfigs = merge(config, webpackConfig);


module.exports = mergedConfigs;
