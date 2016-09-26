var gulp = require('gulp');

//default task
gulp.task('default', ['dist']);

//compress create dist file
gulp.task('dist', function() {
  gulp.src([
        '*files/**/*',
        '*include/**/*',
        'process_mail.php',
        'readme.md'
        ])
      .pipe(gulp.dest('dist/'));
});