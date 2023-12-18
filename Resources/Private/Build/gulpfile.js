const gulp = require('gulp');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const sourcemaps = require('gulp-sourcemaps');

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

gulp.task('classes', function () {
  return gulp.src([
      'JavaScript/Classes.js'
    ])
    .pipe(sourcemaps.init())
    .pipe(concat('Classes.js'))
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('../../Public/JavaScript'));
});

gulp.task('google_be', function () {
  return gulp.src([
      'JavaScript/GoogleMapsModule.js'
    ])
    .pipe(sourcemaps.init())
    .pipe(concat('GoogleMapsModule.min.js'))
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('../../Public/JavaScript'));
});

gulp.task('osm_be', function () {
  return gulp.src([
      'JavaScript/OpenStreetMapModule.js'
    ])
    .pipe(sourcemaps.init())
    .pipe(concat('OpenStreetMapModule.min.js'))
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('../../Public/JavaScript'));
});

gulp.task('google_fe', function () {
  return gulp.src([
      'JavaScript/GoogleMaps2.js'
    ])
    .pipe(sourcemaps.init())
    .pipe(concat('GoogleMaps2.min.js'))
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('../../Public/JavaScript'));
});

gulp.task('osm_fe', function () {
  return gulp.src([
      'JavaScript/OpenStreetMap2.js'
    ])
    .pipe(sourcemaps.init())
    .pipe(concat('OpenStreetMap2.min.js'))
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('../../Public/JavaScript'));
});

gulp.task(
  'default',
  gulp.series(
    'leaflet',
    'classes',
    gulp.parallel('google_be', 'osm_be', 'google_fe', 'osm_fe')
  )
);
