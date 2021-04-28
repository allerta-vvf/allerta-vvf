const { merge } = require("webpack-merge");
const common = require("./webpack.common.js");
const TerserPlugin = require("terser-webpack-plugin");
const SentryWebpackPlugin = require("@sentry/webpack-plugin");
const AfterBuildPlugin = require("@fiverr/afterbuild-webpack-plugin");
const childProcess = require("child_process");
const InjectPlugin = require("webpack-inject-plugin").default;
const colors = require("colors/safe");
const fs = require("fs");
const glob = require("glob");

function git(command) {
  return childProcess.execSync(`git ${command}`, { encoding: "utf8" }).trim();
}
var webpack = require("webpack");

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
      let asset = fs.readFileSync(key, "utf8");
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
};

var configFile = require("./config.json");
const sentryEnabled = configFile.sentryEnabled &&
                       configFile.sentryAuthToken &&
                       configFile.sentryOrganization &&
                       configFile.sentryProject;

var prodConfig = {
  mode: "production",
  devtool: false,
  module: {
      rules: [
          {
            test: /\.m?js$/,
            exclude: /(node_modules|bower_components)/,
            use: {
              loader: "babel-loader",
              options: {
                  presets: ["@babel/preset-env"],
                  plugins: ["@babel/plugin-transform-runtime"]
              }
            }
          }
      ]
  },
  plugins: [],
  optimization: {
      mergeDuplicateChunks: true,
      minimize: true,
      minimizer: [new TerserPlugin({
        parallel: true,
        extractComments: true,
        sourceMap: sentryEnabled ? true : false,
        terserOptions: {
          compress: {
            drop_console: true
          }
        }
      })]
  }
};

module.exports = (env) => {
  //run webpack build with "--env sentryEnvironment=custom-sentry-env" to replace Sentry environment
  if(env.sentryEnvironment){
    console.log(colors.green("INFO using custom sentryEnvironment "+env.sentryEnvironment));
    configFile.sentryEnvironment = env.sentryEnvironment;
  }
  if(!configFile.sentryEnvironment){
    configFile.sentryEnvironment = "prod";
  }

  if(sentryEnabled){
    prodConfig.plugins.push(
      new webpack.SourceMapDevToolPlugin({
        filename: "[file].map"
      }),

      new SentryWebpackPlugin({
        authToken: configFile.sentryAuthToken,
        org: configFile.sentryOrganization,
        project: configFile.sentryProject,
        urlPrefix: "~/dist",
        include: "./dist",
        setCommits: {
          auto: true
        },
        release: "allerta-vvf-frontend@"+git("describe --always")
      }),

      new AfterBuildPlugin(removeSourceMapUrlAfterBuild),

      new InjectPlugin(function() {
        return "import './src/sentry.js';";
      },{ entryName: "main" })
    );
    console.log(colors.green("INFO Sentry Webpack plugins enabled"));
  }

  prodConfig.plugins.push(
    new webpack.EnvironmentPlugin({
      GIT_VERSION: git("describe --always"),
      GIT_AUTHOR_DATE: git("log -1 --format=%aI"),
      BUNDLE_DATE: Date.now(),
      BUNDLE_MODE: "production",
      config: configFile
    })
  );
  return merge(common, prodConfig);
};
