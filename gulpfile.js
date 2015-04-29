var gulp = require('gulp'),
	glob = require('glob'),
	fs = require('fs'),
	Q = require('Q'),
	path = require('path'),
	concat = require('gulp-concat'),
	uglify = require('gulp-uglify'),
	minify = require('gulp-minify-css'),
	sass = require('gulp-sass'),
	extrep = require('gulp-ext-replace');

gulp.task('default', ['scripts', 'styles', 'packages', 'watch']);

gulp.task('watch', function () {
	gulp.watch(
		'assets/src/js/**/*.js',
		['scripts']);
		gulp.watch(
		'assets/src/scss/**/*.scss',
		['styles']);
});

gulp.task('build', ['scripts', 'styles', 'packages']);

gulp.task('scripts', function() {
	var promises = [];

	glob.sync('assets/src/js/!(js)').forEach(function(filePath) {
		if (fs.statSync(filePath).isDirectory()) {
			var defer = Q.defer();
			var pipeline = gulp.src(filePath + '/**/*.js')
				.pipe(concat(path.basename(filePath) + '.js'))
				.pipe(gulp.dest('assets/js'))
				.pipe(uglify())
				.pipe(concat(path.basename(filePath) + '.min.js'))
				.pipe(gulp.dest('assets/js'));
			pipeline.on('end', function() {
				defer.resolve();
			});
			promises.push(defer.promise);
		}
	});

	return Q.all(promises);
});

gulp.task('styles', function() {
	return gulp.src('assets/scss/*.scss')
		.pipe(sass())
		.pipe(gulp.dest('assets/css'))
		.pipe(minify())
		.pipe(extrep('.min.css'))
		.pipe(gulp.dest('assets/css'));
});

gulp.task('packages', ['package1']);

gulp.task('package1', function() {
	// Copy or concat your packages
	// and place them in the appropriate assets folder
});
