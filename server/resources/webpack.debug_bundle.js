const { merge } = require("webpack-merge");
const prod = require("./webpack.prod.js");
const SpeedMeasurePlugin = require("speed-measure-webpack-plugin");
const smp = new SpeedMeasurePlugin();
const BundleAnalyzerPlugin = require("webpack-bundle-analyzer").BundleAnalyzerPlugin;

module.exports = smp.wrap(merge(prod, {
    plugins: [
        new BundleAnalyzerPlugin({
            analyzerMode: "static",
            openAnalyzer: true,
            generateStatsFile: true
        })
    ]
}));