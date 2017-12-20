/*global require */
/*eslint no-console: 1 */
"use strict";
const gulp = require( 'gulp' );
const uglify = require( 'gulp-uglify' );
const rename = require( 'gulp-rename' );
const sass = require( 'gulp-sass' );
const cssnano = require( 'gulp-cssnano' );
const imagemin = require( 'gulp-imagemin' );
const svgmin = require( 'gulp-svgmin' );
const mq = require( 'gulp-combine-mq' );
const autoprefixer = require( 'gulp-autoprefixer' );
const zip = require( 'gulp-zip' );
const childProcess = require( 'child_process' );
const util = require( 'gulp-util' );
const fs = require( 'fs' );
const CONFIG = JSON.parse( fs.readFileSync( './package.json' ) ).config;

const createExecCallback = ( cb ) => ( err, stdout, stderr ) => {
	if ( stdout ) {
		util.log( stdout );
	}
	if ( stderr ) {
		util.log( stderr );
	}
	cb( err );
};

gulp.task( 'scripts', function() {
	const dest = CONFIG.scripts.dest;

	gulp.src( [ CONFIG.scripts.src + '/*.js' ] )
		.pipe( gulp.dest( dest ) )
		.pipe( rename( { extname: '.min.js' } ) )
		.pipe( uglify( { output: { ascii_only: true } } ) )
		.pipe( gulp.dest( dest ) );
} );

gulp.task( 'styles', function() {
	gulp.src( CONFIG.styles.src + '*.scss' )
		.pipe( sass( {
			indentType : 'tab',
			indentWidth: 1,
			outputStyle: 'expanded'
		} ) )
		.pipe( mq() )
		.pipe( autoprefixer( { cascade: false } ) )
		.pipe( gulp.dest( CONFIG.styles.dest ) )
		.pipe( cssnano( { 'zindex': false } ) )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( gulp.dest( CONFIG.styles.dest ) );
} );

gulp.task( 'svn-assets', () => {
	const dest = CONFIG.svn_assets.dest;

	return gulp.src( `${CONFIG.svn_assets.src}*.{gif,jpeg,jpg,png}` )
		.pipe( imagemin( { optimizationLevel: 7 } ) )
		.pipe( gulp.dest( dest ) );
} );

gulp.task( 'images', () => {
	const dest = CONFIG.images.dest;

	return gulp.src( `${CONFIG.images.src}*.{gif,jpeg,jpg,png}` ).pipe( imagemin( { optimizationLevel: 7 } ) ).pipe( gulp.dest( dest ) )
		&& gulp.src( `${CONFIG.images.src}*.svg` ).pipe( svgmin() ).pipe( gulp.dest( dest ) );
} );

gulp.task( 'composer-production', ( cb ) => {
	// we don't want scripts and dev-dependencies in our release.
	childProcess.exec( 'composer install --no-ansi --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader', createExecCallback( cb ) );
} );
gulp.task( 'composer-develop', ( cb ) => {
	// we don't want scripts and dev-dependencies in our release.
	childProcess.exec( 'composer install', createExecCallback( cb ) );
} );

// Shortcut to build all assets.
gulp.task( 'assets', [ 'images', 'scripts', 'styles' ] );

// Can be used to setup locally the plugin with all required dependencies and scripts/styles.
gulp.task( 'develop', [ 'assets', 'composer-develop' ] );

// Create a release with build assets and composer production-dependencies.
gulp.task( 'release', [ 'assets', 'composer-production' ], () => {

	return gulp
		.src( [
			`${CONFIG.images.dest}**/*`,
			`${CONFIG.scripts.dest}*.js`,
			`${CONFIG.styles.dest}*.css`,
			'src/**/*.php',
			'vendor/**/*.php',
			'*.{php,txt}',
			'LICENSE',
			'!report-*.txt',
			'!vendor/**/**/tests{,/**}',
		], {
			base: '.'
		} )
		.pipe( rename( ( path ) => {
			path.dirname = `${CONFIG.slug}/${path.dirname}`;
		} ) )
		.pipe( zip( `${CONFIG.name}.zip` ) )
		.pipe( gulp.dest( '.' ) );
} );