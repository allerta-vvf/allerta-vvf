const path = require('path');
const CopyPlugin = require('copy-webpack-plugin');
const WebpackAssetsManifest = require('webpack-assets-manifest');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = {
  entry: {
    main: path.resolve(__dirname, './src/main.js'),
    maps: path.resolve(__dirname, './src/maps.js'),
    players: path.resolve(__dirname, './src/players.js'),
    sw: path.resolve(__dirname, './src/sw.js'),
  },
  output: {
    filename: (pathData) => {
      return pathData.chunk.name === 'sw' ? '../../sw.js': '[name].[contenthash].js';
    },
    path: path.resolve(__dirname, 'dist'),
    publicPath: 'resources/dist/',
  },
  resolve: {
    alias: {
      // Force all modules to use the same jquery version.
      'jquery': path.join(__dirname, 'node_modules/jquery/src/jquery')
    }
  },
  module: {
    rules: [
      {
        test: /\.css$/i,
        use: ['style-loader', 'css-loader'],
      },
      {
        test: /\.s(a|c)ss$/,
        use: ['style-loader', 'css-loader', 'sass-loader'],
      },
      {
        test: require.resolve('jquery'),
        loader: 'expose-loader',
        options: {
          exposes: ['$', 'jQuery'],
        },
      },
      {
        test: /\.(gif|png|jpg)(\?v=\d+\.\d+\.\d+)?$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '[name].[ext]',
              outputPath: './'
            }
          }
        ]
      },
      {
        test: /\.(ttf|otf|eot|svg|woff(2)?)(\?[a-z0-9]+)?$/,
        use: [{
          loader: 'file-loader',
          options: {
            name: '[name].[ext]',
            outputPath: 'fonts/',
            publicPath: 'resources/dist/fonts'
          }
        }]
      }
    ],
  },
  plugins: [
    new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
    new CleanWebpackPlugin(),
    new CopyPlugin({
      patterns: [
        { from: 'node_modules/leaflet/dist/images', to: '.', noErrorOnMissing: true }
      ],
    }),
    new WebpackAssetsManifest()
  ],
  optimization: {
    mergeDuplicateChunks: true
  }
};