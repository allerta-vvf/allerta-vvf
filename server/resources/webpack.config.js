const path = require('path');
var webpack = require('webpack');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = {
  entry: './src/src.js',
  output: {
    filename: 'main.js',
    path: path.resolve(__dirname, 'dist'),
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
        test: /\.(woff(2)?|ttf|eot|svg|png|jpg)(\?v=\d+\.\d+\.\d+)?$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '[name].[ext]',
              outputPath: './'
            }
          }
        ]
      }
    ],
  },
  plugins: [
    new CleanWebpackPlugin(),
    new webpack.ProvidePlugin({
        jQuery: 'jquery',
        $: 'jquery',
        popper: 'popper.js'
    }),
  ],
};