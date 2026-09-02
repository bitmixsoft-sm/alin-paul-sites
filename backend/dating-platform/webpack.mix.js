const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css');

mix.styles([
  'public/Bootstrap/dist/css/bootstrap-reboot.css',
  'public/Bootstrap/dist/css/bootstrap.css',
  'public/Bootstrap/dist/css/bootstrap-grid.css',
  'public/css/main.css',
  'public/css/croppie.css',
  'public/css/fonts.min.css',
  'public/css/style.css'
], 'public/css/compressed.css');