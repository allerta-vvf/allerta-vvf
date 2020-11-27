const { merge } = require('webpack-merge');
const common = require('./webpack.common.js');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const child_process = require('child_process');
function git(command) {
  return child_process.execSync(`git ${command}`, { encoding: 'utf8' }).trim();
}
var webpack = require('webpack');

module.exports = merge(common, {
    mode: 'production',
    module: {
        rules: [
            {
              test: /\.m?js$/,
              exclude: /(node_modules|bower_components)/,
              use: {
                loader: 'babel-loader',
                options: {
                    presets: ['@babel/preset-env'],
                    plugins: ['@babel/plugin-transform-runtime']
                }
              }
            }
        ]
    },
    plugins: [
        new webpack.EnvironmentPlugin({
            GIT_VERSION: git('describe --always'),
            GIT_AUTHOR_DATE: git('log -1 --format=%aI'),
            BUNDLE_DATE: Date.now(),
            BUNDLE_MODE: 'production'
        })
    ],
    optimization: {
        mergeDuplicateChunks: true,
        minimize: true,
        minimizer: [new UglifyJsPlugin({
          parallel: true,
          extractComments: true
        })]
    }
});