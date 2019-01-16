let gulp       = require( 'gulp' );
let sass       = require( 'gulp-sass' );
let rename     = require( 'gulp-rename' );
let sourcemaps = require( 'gulp-sourcemaps' );
let bulkSass   = require( 'gulp-sass-bulk-import' );

let srcDir  = './src';
let destDir = './feature-flags';
let paths   = {
    scss: {
        input: [ `${srcDir}/scss/feature-flags.scss` ],
        output: `${destDir}/assets/css/`,
        watch: `${srcDir}/scss/**/*.scss`
  }
};

gulp.task( 'scss', function( done ) {

  gulp.src( './src/scss/feature-flags.scss' )
    .pipe( bulkSass() )
    .pipe( sourcemaps.init() )
    .pipe( sass({

      outputStyle: 'compact',
      includePaths: [ 'src/scss' ]

    }))
    .on( 'error', sass.logError )
    .pipe( rename({ extname: '.css' }) )
    .pipe( sourcemaps.write( './', {

      includeContent: false

    }))
    .pipe( gulp.dest( paths.scss.output ) )
    .on( 'end', done );
});
