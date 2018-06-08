var gulp = require("gulp");
var sass = require('gulp-sass');
var rename = require('gulp-rename');
var sourcemaps = require('gulp-sourcemaps');
var bulkSass = require('gulp-sass-bulk-import');
var concat = require('gulp-concat');
var uglify = require('gulp-uglifyjs');

var paths = {

  scss: ['./_build/scss/**/*.scss'],
  js: ['./_build/**/*.js']

};

gulp.task('default', function () {

  console.log("Hello there...");

});

gulp.task('scss', function (done) {

  gulp.src('./_build/scss/feature-flags.scss')
    .pipe(bulkSass())
    .pipe(sourcemaps.init())
    .pipe(sass({

      outputStyle: 'compressed',
      includePaths: ['src/scss']

    }))
    .on('error', sass.logError)
    .pipe(rename({ extname: '.css' }))
    .pipe(sourcemaps.write('./', {

      includeContent: false

    }))
    .pipe(gulp.dest('./assets/css/'))
    .on('end', done);

});
gulp.task('js', function (done) {

  gulp.src('./_build/js/feature-flags.js')
    .pipe(concat('feature-flags.js'))
    .pipe(uglify('feature-flags.js'))
    .pipe(sourcemaps.init())
    .pipe(sourcemaps.write('.', {

      includeContent: false,
      sourceRoot: '.'

    }))
    .pipe(gulp.dest('./assets/js/'))
    .on('end', done);

});

gulp.task('watch', function () {

  gulp.watch(paths.scss, ['scss']);
  gulp.watch(paths.js, ['js']);

});