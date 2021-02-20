const { merge } = require('webpack-merge');
const common = require('./webpack.common.js');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const SentryWebpackPlugin = require("@sentry/webpack-plugin");
const AfterBuildPlugin = require('@fiverr/afterbuild-webpack-plugin');
const child_process = require('child_process');
const InjectPlugin = require('webpack-inject-plugin').default;
const colors = require('colors/safe');
const fs = require('fs');
const glob = require('glob');

function git(command) {
  return child_process.execSync(`git ${command}`, { encoding: 'utf8' }).trim();
}
var webpack = require('webpack');

if (!fs.existsSync("config.json")) {
  fs.copyFileSync("config_sample.json", "config.json");
}

const removeSourceMapUrlAfterBuild = () => {
  //based on @rbarilani https://github.com/rbarilani/remove-source-map-url-webpack-plugin

  glob("./dist/*.js", function (er, files) {
    let countMatchAssets = 0;
    files.push("../sw.js");
    //console.log(files);
    files.forEach((key) => {
      countMatchAssets += 1;
      let asset = fs.readFileSync(key, 'utf8');
      let source = asset.split("//# sourceMappingURL=")[0].replace(/\n$/, "");
      fs.writeFileSync(key, source);
    });

    if (countMatchAssets) {
      console.log(colors.green(`remove-source-map-url: ${countMatchAssets} asset(s) processed`));
    }
  });
  glob("./dist/*.js.map", function (er, files) {
    files.push("../sw.js.map");
    files.forEach((key) => {
      fs.unlinkSync(key);
    });
  });
}

var config_file = require('./config.json');
const sentry_enabled = config_file.sentry_enabled &&
                       config_file.sentry_auth_token &&
                       config_file.sentry_organization &&
                       config_file.sentry_project;

var prod_config = {
  mode: 'production',
  devtool: false,
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
  plugins: [],
  optimization: {
      mergeDuplicateChunks: true,
      minimize: true,
      minimizer: [new UglifyJsPlugin({
        parallel: true,
        extractComments: true,
        sourceMap: sentry_enabled ? true : false
      })]
  }
};

module.exports = (env) => {
  //run webpack build with '--env sentry_environment=custom-sentry-env' to replace Sentry environment
  if(env.sentry_environment){
    console.log(colors.green("INFO using custom sentry_environment "+env.sentry_environment));
    config_file.sentry_environment = env.sentry_environment;
  }
  if(!config_file.sentry_environment){
    config_file.sentry_environment = "prod";
  }

  if(sentry_enabled){
    prod_config.plugins.push(
      new webpack.SourceMapDevToolPlugin({
        filename: '[file].map'
      }),

      new SentryWebpackPlugin({
        authToken: config_file.sentry_auth_token,
        org: config_file.sentry_organization,
        project: config_file.sentry_project,
        urlPrefix: '~/dist',
        include: './dist',
      }),

      new AfterBuildPlugin(removeSourceMapUrlAfterBuild),

      new InjectPlugin(function() {
        return "import './src/sentry.js';";
      },{ entryName: 'main' })
    );
    console.log(colors.green("INFO Sentry Webpack plugins enabled"));
  }
  
  prod_config.plugins.push(
    new webpack.EnvironmentPlugin({
      GIT_VERSION: git('describe --always'),
      GIT_AUTHOR_DATE: git('log -1 --format=%aI'),
      BUNDLE_DATE: Date.now(),
      BUNDLE_MODE: 'production',
      config: config_file
    })
  );
  return merge(common, prod_config);
}