var gulp = require('gulp');
var runSequence = require('run-sequence');
var bower = require('gulp-bower');
var jshint = require('gulp-jshint');
var jscs = require('gulp-jscs');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var clean = require('gulp-clean');
var istanbul = require('gulp-istanbul');
var mocha = require('gulp-mocha');
var tap = require('gulp-tap');
var mainBowerFiles = require('main-bower-files');
var jade = require('gulp-jade');
var sass = require('gulp-sass');
var rev = require('gulp-rev');
var revReplace = require('gulp-rev-replace');

var testFiles = ['test/**/*.test.js'];
var sourceCode = ['gulpfile.js', './src/frontend/**/*.js'];
var jadeTemplates = ['./src/views/*.jade'];
var cssSourceCode = ['../web/css/**/*.scss'];
var frontendSourceCode = ['./src/frontend/**/*.js'];
var coverageReportDir = 'coverage-report';
var distDirectory = '../web/dist';
var distScriptsDirectory = '../web/dist/js';
var distHtmlDirectory = '../web';
var bowerInstallDirectory = '../web/dist/bower_components';
var revManifestFilename = 'rev-manifest.json';
var vendorsFilename = 'vendors.js';
var appFilename = 'app.js';
var compiledScriptsFilename = 'all.js';

// default
gulp.task('lint', function() {
    return gulp.src(sourceCode)
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'));
});

gulp.task('jscs', function() {
    return gulp.src(sourceCode.concat(testFiles))
        .pipe(jscs());
});

gulp.task('test:unit', function() {
    return gulp.src(testFiles, { read: false })
        .pipe(mocha());
});

gulp.task('code:coverage', function(cb) {
    gulp.src(sourceCode)
        .pipe(istanbul({ includeUntested: true }))
        .pipe(istanbul.hookRequire())
        .on('finish', function() {
            gulp.src(testFiles)
                .pipe(mocha())
                .pipe(istanbul.writeReports({
                    dir: coverageReportDir
                }))
                .on('end', cb);
        });
});

// build
gulp.task('dist:clean', function() {
    return gulp.src(distDirectory, { read: false })
        .pipe(clean());
});

gulp.task('bower:install', function() {
    return bower(bowerInstallDirectory)
        .pipe(gulp.dest(bowerInstallDirectory));
});

gulp.task('bower:compile', ['bower:install'], function() {
    return gulp.src(mainBowerFiles({
            paths: {
                bowerDirectory: bowerInstallDirectory,
                bowerJson: './bower.json'
            }
        }))
        .pipe(concat(vendorsFilename))
        .pipe(uglify())
        .pipe(gulp.dest(distScriptsDirectory));
});

gulp.task('app:compile', function() {
    return gulp.src(frontendSourceCode)
        .pipe(concat(appFilename))
        .pipe(uglify())
        .pipe(gulp.dest(distScriptsDirectory));
});

gulp.task('assembly', ['app:compile'], function() {
    var files = [
        distScriptsDirectory + '/' + vendorsFilename,
        distScriptsDirectory + '/' + appFilename
    ];

    return gulp.src(files)
        .pipe(concat(compiledScriptsFilename))
        .pipe(gulp.dest(distScriptsDirectory))
        .pipe(rev())
        .pipe(gulp.dest(distScriptsDirectory))
        .pipe(rev.manifest(revManifestFilename))
        .pipe(gulp.dest(distDirectory));
});

gulp.task('rev:replace', function() {
    var manifest = gulp.src(distDirectory + '/' + revManifestFilename);

    return gulp.src(distHtmlDirectory + '/index.html')
        .pipe(revReplace({manifest: manifest}))
        .pipe(gulp.dest(distHtmlDirectory));
});

gulp.task('default', ['lint', 'jscs', 'test:unit']);

gulp.task('angular:compile', function() {
    return gulp.src(frontendSourceCode)
        .pipe(concat(appFilename))
        .pipe(uglify())
        .pipe(gulp.dest(distScriptsDirectory));
});

gulp.task('angular:watch', function() {
    gulp.watch(frontendSourceCode, ['angular:compile']);
});

gulp.task('jade:compile', function() {
    return gulp.src(jadeTemplates)
        .pipe(jade(jadeTemplates))
        .pipe(gulp.dest(distHtmlDirectory));
});

gulp.task('jade:watch', function() {
    gulp.watch('./src/views/**/*.jade', ['jade:compile']);
});

gulp.task('css:compile', function() {
    return gulp.src(cssSourceCode)
        .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
        .pipe(gulp.dest('../web/dist/stylesheets'));
});

gulp.task('css:watch', function() {
    gulp.watch(cssSourceCode, ['css:compile']);
});

gulp.task('watch', function(done) {
    runSequence(
        'jade:watch',
        'css:watch',
        'angular:watch'
    );
});

gulp.task('app:watch', function() {
    gulp.watch(frontendSourceCode, ['assembly', 'jade:compile']);
});

gulp.task('build', function(done) {
    runSequence(
        'dist:clean',
        'jade:compile',
        ['bower:compile', 'app:compile'],
        'assembly',
        'rev:replace',
        'css:compile',
        done
    );
});
