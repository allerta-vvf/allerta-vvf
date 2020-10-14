const path = require('path');
var webpack = require('webpack');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
//const workboxPlugin = require('workbox-webpack-plugin');

module.exports = {
  entry: {
    main: path.resolve(__dirname, './src/main.js'),
    maps: path.resolve(__dirname, './src/maps.js'),
    sw: path.resolve(__dirname, './src/sw.js'),
  },
  output: {
    filename: (pathData) => {
      return pathData.chunk.name === 'sw' ? '../../sw.js': '[name].js';
    },
    path: path.resolve(__dirname, 'dist'),
    publicPath: '/resources/dist/',
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
        test: require.resolve('pickadate'),
        loader: 'expose-loader',
        options: {
          exposes: ['pickadate'],
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
    new CleanWebpackPlugin(),
    new webpack.ProvidePlugin({
        $: 'jquery',
        popper: 'popper.js'
    })
  ],
  optimization: {
    mergeDuplicateChunks: true
  },
};