gulp = require('gulp')
coffee = require('gulp-coffee')
coffeelint = require('gulp-coffeelint')
concat = require('gulp-concat')
less = require('gulp-less')
cssmin = require('gulp-cssmin')
argv = require('yargs')
check = require('gulp-if')
uglify = require('gulp-uglify')
rimraf = require('gulp-rimraf')
replace = require('gulp-replace')

gulp.task 'styles-vendors', ->
  gulp.src [
    'assets/bower/select2/{select2,select2-bootstrap}.css',
    'assets/bower/bootstrap-datepicker/css/datepicker3.css',
    'assets/bower/jquery-colorbox/example1/colorbox.css',
  ]
    .pipe replace(/images\/(.*?)\.(png|gif)/g, '../images/$1.$2')
    .pipe replace(/select2(.*?)\.(png|gif)/g, '../images/select2$1.$2')
    .pipe cssmin()
    .pipe concat('vendors.min.css')
    .pipe gulp.dest('assets/css')

gulp.task 'styles', ['styles-vendors'], ->
  gulp.src 'assets/less/**/*.less'
    .pipe less()
    .pipe check(argv.production, cssmin())
    .pipe gulp.dest('assets/css')

gulp.task 'scripts-vendors', ->
  gulp.src [
    'assets/bower/select2/select2.js',
    'assets/bower/bootstrap/js/{tab,transition,tooltip}.js',
    'assets/bower/bootstrap-datepicker/js/bootstrap-datepicker.js',
    'assets/bower/jquery-colorbox/jquery.colorbox-min.js',
  ]
    .pipe uglify()
    .pipe concat('vendors.min.js')
    .pipe gulp.dest('assets/js')

gulp.task 'scripts', ['lint', 'scripts-vendors'], ->
  gulp.src 'assets/coffee/**/*.coffee'
    .pipe coffee({bare: true})
    .pipe check(argv.production, uglify())
    .pipe gulp.dest('assets/js')

gulp.task 'lint', ->
  gulp.src 'assets/coffee/**/*.coffee'
    .pipe coffeelint('coffeelint.json')
    .pipe coffeelint.reporter()
    .pipe coffeelint.reporter('fail')

gulp.task 'fonts', ->
  gulp.src 'assets/bower/bootstrap/fonts/*'
    .pipe gulp.dest('assets/fonts')

gulp.task 'clean', ->
  gulp.src ['assets/css/*', '!assets/js/flot', '!assets/js/flot/**', '!assets/js/blockui.js', 'assets/js/*',  'assets/fonts'], {read: false}
    .pipe rimraf()

gulp.task 'watch', ['styles', 'scripts', 'fonts'], ->
  gulp.watch ['assets/coffee/**/*.coffee'], ['scripts']
  gulp.watch ['assets/less/**/*.less'], ['styles']

gulp.task 'clean-deploy', ->
  gulp.src ['dist/*'], {read: false}
  .pipe rimraf()

gulp.task 'dist', ['clean-deploy', 'default'], ->
  gulp.src ['./assets/**/*', '!assets/{bower,coffee,less}', '!assets/{bower,coffee,less}/**', './cache', './integration/**/*',
            './config/**/*', './languages/**/*', './src/**/*', './templates/**/*', './log', './vendor/**/*', './CHANGELOG.md',
            './CONTRIBUTING.md', 'LICENCE.md', 'README.md', 'jigoshop.php'], {base: './'}
    .pipe gulp.dest('dist/')

gulp.task 'default', ['styles', 'scripts', 'fonts']
