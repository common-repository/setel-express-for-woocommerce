let mix = require('laravel-mix')

mix.webpackConfig({
  externals: {
    'jquery': 'jQuery',
  }
})

mix.postCss('admin/css/setel-express-admin.css', 'admin/css/setel-express-admin-dist.css', [
  require('tailwindcss'),
])

mix.js('admin/js/setel-express-admin.js', 'admin/js/setel-express-admin-dist.js')