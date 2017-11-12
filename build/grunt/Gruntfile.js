module.exports = function (grunt) {
	var _package = grunt.file.readJSON('package.json');

	grunt.initConfig({
		pkg: _package,

		concat: {
			js: {
				options: {
					separator: ''
				},
				src:  _package.build_directory + '/js/*.js',
				dest: _package.dist_directory + '/global.cat.js'
			},
			scss: {
				options: {
					separator: ''
				},
				src:  _package.build_directory + '/scss/*.scss',
				dest: _package.dist_directory + '/global.cat.scss'
			}
		},

		//Minify JS
		uglify: {
			main: {
				options: {
					beautify: false
				},
				files: {
					'../../assets/global.min.js': _package.dist_directory + '/global.cat.js'
				}
			}
		},

		// Compile SCSS into CSS
		sass: {
			main: {
				files: {
					'../../assets/global.min.css': _package.dist_directory + '/global.cat.scss'
				}
			}
		},

		//Add vendor prefixes
		postcss: {
			options: {
				//map:false,
				processors: [
					require('postcss-discard-comments'),
					require('rucksack-css')({
						fallbacks: true
					}),
					require('css-mqpacker'),
					require('cssnano')({
						safe: true
					}),
					require('autoprefixer')({
						browsers: 'last 5 versions'
					})
				]
			},
			dist: {
				src: _package.dist_directory + '/*.css'
			}
		},

		// Remove .cat and other files created by other grunt tasks that aren't needed
		clean: {
			js: [_package.dist_directory + "/global.cat.js", _package.dist_directory + "/admin.cat.js"],
			scss: [
				_package.dist_directory + "/global.css",
				_package.dist_directory + "/global.cat.scss",
				_package.dist_directory + "/global.min.css.map",
			],
			options: {
				force:true
			}
		},

		watch: {
			scss: {
				files: _package.build_directory + '/scss/*.scss',
				tasks: ['concat:scss', 'sass:main', 'postcss', 'clean:scss'],
				options: {
					interrupt: true,
					livereload: true,
				}
			},
			js: {
				files:  _package.build_directory + '/js/*.js',
				tasks: ['concat:js', 'uglify:main', 'clean:js']
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-postcss');

	grunt.registerTask('default', ['concat', 'uglify', 'sass', 'postcss', 'clean', 'watch']);
};
