const gulp = require('gulp');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const sourcemaps = require('gulp-sourcemaps');
const ts = require('gulp-typescript');
const tsProject = ts.createProject('TypeScript/tsconfig.json');

// Define a task to process TypeScript files
gulp.task('typescript', function () {
  return tsProject.src()
    .pipe(sourcemaps.init())
    .pipe(tsProject())
    .js
    .pipe(gulp.dest('TypeScript/dist'));
});

gulp.task('leaflet', function buildLeaflet () {
  const paths = [
    'node_modules/leaflet/dist/leaflet.js',
    'node_modules/leaflet.path.drag/src/Path.Drag.js',
    'node_modules/leaflet-editable/src/Leaflet.Editable.js'
  ];

  return gulp.src(paths)
    .pipe(sourcemaps.init())
    .pipe(concat('leaflet.min.js'))
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('../../Public/JavaScript'));
});

gulp.task('main', function () {
  return gulp.src([
    'TypeScript/dist/OpenStreetMap2.js',
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('OpenStreetMap2.min.js'))
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('../../Public/JavaScript'));
});

gulp.task('default', gulp.series(gulp.parallel('typescript', 'leaflet'), 'main'));
