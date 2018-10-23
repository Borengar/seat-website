const path = require('path')
const CleanWebpackPlugin = require('clean-webpack-plugin')
const HtmlWebpackPlugin = require('html-webpack-plugin')
const VueLoaderPlugin = require('vue-loader/lib/plugin')
const CopyWebpackPlugin = require('copy-webpack-plugin')

module.exports = {
	mode: 'development',
	entry: {
		app: './src/index.js'
	},
	watch: true,
	watchOptions: {
		ignored: [
			'node_modules'
		]
	},
	devtool: 'inline-source-map',
	plugins: [
		new CleanWebpackPlugin(['build']),
		new HtmlWebpackPlugin({
			filename: 'index.html',
			template: './src/index.html',
			inject: true
		}),
		new VueLoaderPlugin(),
		new CopyWebpackPlugin([
			{ from: './src/.htaccess', to: '' }
		])
	],
	output: {
		path: path.resolve(__dirname, 'build'),
		filename: '[name].js',
		publicPath: '/'
	},
	resolve: {
		extensions: ['.js', '.vue', '.json'],
		alias: {
			'vue$': 'vue/dist/vue.esm.js'
		}
	},
	module: {
		rules: [
			{
				test: /\.vue$/,
				loader: 'vue-loader'
			},
			{
				test: /\.pug$/,
				loader: 'pug-plain-loader'
			},
			{
				test: /\.stylus$/,
				use: [
					'vue-style-loader',
					'css-loader',
					'stylus-loader'
				]
			},
			{
				test: /\.styl$/,
				use: [
					'style-loader',
					'css-loader',
					'stylus-loader'
				]
			},
			{
				test: /\.(png|svg|jpg|gif)$/,
				loader: 'file-loader'
			},
			{
				test: /\.css$/,
				use: [
					'style-loader',
					'css-loader'
				]
			}
		]
	}
}