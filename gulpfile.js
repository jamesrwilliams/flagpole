let gulp = require("gulp");
let sass = require('gulp-sass');
let rename = require('gulp-rename');
let sourcemaps = require('gulp-sourcemaps');
let bulkSass = require('gulp-sass-bulk-import');
let babel = require('gulp-babel');
let concat = require('gulp-concat');
let uglify = require('gulp-uglifyjs');

let src_dir = './src';
let dest_dir = './feature-flags';
let paths = {

  scss: {
      input: [`${src_dir}/scss/feature-flags.scss`],
      output: `${dest_dir}/assets/css/`,
      watch: `${src_dir}/scss/**/*.scss`
  },
  js: {
      input: [`${src_dir}/**/*.js`],
      output: `${dest_dir}/assets/js/`,
      watch: `${src_dir}/**/*.js`
  }

};

gulp.task('scss', function (done) {

  gulp.src('./src/scss/feature-flags.scss')
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
    .pipe(gulp.dest(paths.scss.output))
    .on('end', done);

});
gulp.task('js', function (done) {

  gulp.src(paths.js.input)
    .pipe(concat('feature-flags.js'))
    .pipe(sourcemaps.init())
    .pipe(babel({
      'presets': [ [ 'env', {
          'targets': {
              'browsers': [ 'last 2 versions', 'ie 11' ]
          }
      } ] ]
    }))
    .pipe(uglify('feature-flags.js'))
    .pipe(sourcemaps.write('.', {

      includeContent: false,
      sourceRoot: '.'

    }))
    .pipe(gulp.dest(paths.js.output))
    .on('end', done);

});

gulp.task('watch', function () {

  gulp.watch(paths.scss.watch, ['scss']);
  gulp.watch(paths.js.watch, ['js']);

});
