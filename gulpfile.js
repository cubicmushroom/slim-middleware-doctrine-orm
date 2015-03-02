var gulp        = require('gulp'),
    bump        = require('gulp-bump'),
    confirm     = require('gulp-confirm'),
    filter      = require('gulp-filter'),
    git         = require('gulp-git'),
    tag_version = require('gulp-tag-version');

var changelogSupportFilePattern = './CHANGELOG',
    versionFilePattern          = ['./composer.json', './package.json'];

/**
 * Version Tagging tasks
 */
/**
 * Bumping version number and tagging the repository with it.
 * Please read http://semver.org/
 *
 * You can use the commands
 *
 *     gulp patch     # makes v0.1.0 → v0.1.1
 *     gulp feature   # makes v0.1.1 → v0.2.0
 *     gulp release   # makes v0.2.1 → v1.0.0
 *
 * To bump the version numbers accordingly after you did a patch,
 * introduced a feature or made a backwards-incompatible release.
 */

function incrementVersion(importance) {
    // get all the files to bump version in
    return gulp.src(versionFilePattern)
        // bump the version number in those files
        .pipe(bump({type: importance}))
        // save it back to filesystem
        .pipe(gulp.dest('./'))
        // commit the changed version number
        .pipe(git.commit('Released updated version'))
        // read only one file to get the version number
        .pipe(filter('package.json'))
        // **tag it in the repository**
        .pipe(tag_version());
}

gulp.task('confirm:changelog', function () {
    return gulp.src(versionFilePattern)
        .pipe(confirm({
            question  : 'Have you updated the CHANGELOG?',
            "continue": function (answer) {
                return answer.toLowerCase() === 'y';
            }
        }));
});
gulp.task('commit:changelog', function () {
    return gulp.src(changelogSupportFilePattern)
        .pipe(git.commit('Updating CHANGELOG'));
});
gulp.task('version:patch', ['confirm:changelog', 'commit:changelog'], function () {
    return incrementVersion('patch');
});
gulp.task('version:feature', ['confirm:changelog', 'commit:changelog'], function () {
    return incrementVersion('minor');
});
gulp.task('version:release', ['confirm:changelog', 'commit:changelog'], function () {
    return incrementVersion('major');
});