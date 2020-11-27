const { merge } = require('webpack-merge');
const common = require('./webpack.common.js');
var webpack = require('webpack');

module.exports = merge(common, {
    mode: 'development',
    devtool: false,
    devServer: {
        contentBase: './dist',
    },
    plugins: [
        new webpack.EnvironmentPlugin({
            GIT_VERSION: null,
            GIT_AUTHOR_DATE: null,
            BUNDLE_DATE: Date.now(),
            BUNDLE_MODE: 'development'
        })
    ]
});