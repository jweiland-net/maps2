const gulp = require('gulp');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const sourcemaps = require('gulp-sourcemaps');
const ts = require('gulp-typescript');
const tsProject = ts.createProject('Resources/Private/TypeScript/tsconfig.json');

// Define a task to process TypeScript files
gulp.task('typescript', function () {
  return tsProject.src()
    .pipe(sourcemaps.init())
    .pipe(tsProject())
    .js
    .pipe(gulp.dest('Resources/Private/TypeScript/dist'));
});

gulp.task('leaflet', function () {
  return gulp.src([
    'node_modules/leaflet/dist/leaflet.js',
    'node_modules/leaflet.path.drag/src/Path.Drag.js',
    'node_modules/leaflet-editable/src/Leaflet.Editable.js',
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('leaflet.min.js'))
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('Resources/Public/JavaScript'));

});

gulp.task('main', function () {
  return gulp.src([
    'Resources/Private/TypeScript/dist/OpenStreetMap2.js',
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('OpenStreetMap2.min.js'))
    //.pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('Resources/Public/JavaScript'));
});

gulp.task('default', gulp.series(gulp.parallel('typescript', 'leaflet'), 'main'));
