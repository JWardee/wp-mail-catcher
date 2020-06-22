module.exports = function(grunt) {
  var _package = grunt.file.readJSON('package.json');

  grunt.initConfig({
    pkg: _package,

    pot: {
      options: {
        text_domain: _package.name,
        dest: _package.lang_po_directory,
        msgid_bugs_address: _package.support_email,
        keywords: [ //WordPress localisation functions
          '__:1',
          '_e:1',
          '_x:1,2c',
          'esc_html__:1',
          'esc_html_e:1',
          'esc_html_x:1,2c',
          'esc_attr__:1',
          'esc_attr_e:1',
          'esc_attr_x:1,2c',
          '_ex:1,2c',
          '_n:1,2',
          '_nx:1,2,4c',
          '_n_noop:1,2',
          '_nx_noop:1,2,3c'
        ],
      },
      files: {
        src: [_package.src_directory + '/**/*.php'],
        expand: true,
      },
    },

    // TODO: Incorporate globbing
    // FIXME: po2mo compile fails on node versions > 10, need to manually generate mo file for now ("Can not create sync-exec directory")
    po2mo: {
      files: {
        src: _package.lang_po_directory + '/WpMailCatcher-fr_FR.po',
        dest: _package.lang_po_directory + '/WpMailCatcher-fr_FR.mo',
      },
    },

    concat: {
      js: {
        options: {
          separator: '',
        },
        src: _package.build_directory + '/js/*.js',
        dest: _package.dist_directory + '/global.cat.js',
      },
      scss: {
        options: {
          separator: '',
        },
        src: _package.build_directory + '/scss/*.scss',
        dest: _package.dist_directory + '/global.cat.scss',
      },
    },

    //Minify JS
    uglify: {
      main: {
        options: {
          beautify: false,
        },
        files: {
          '../../assets/global.min.js': _package.dist_directory +
              '/global.cat.js',
        },
      },
    },

    // Compile SCSS into CSS
    sass: {
      main: {
        files: {
          '../../assets/global.min.css': _package.dist_directory +
              '/global.cat.scss',
        },
      },
    },

    //Add vendor prefixes
    postcss: {
      options: {
        //map:false,
        processors: [
          require('postcss-discard-comments'),
          require('rucksack-css')({
            fallbacks: true,
          }),
          require('css-mqpacker'),
          require('cssnano')({
            safe: true,
          }),
          require('autoprefixer'),
        ],
      },
      dist: {
        src: _package.dist_directory + '/*.css',
      },
    },

    // Remove .cat and other files created by other grunt tasks that aren't needed
    clean: {
      js: [
        _package.dist_directory + '/global.cat.js',
        _package.dist_directory + '/admin.cat.js'],
      scss: [
        _package.dist_directory + '/global.css',
        _package.dist_directory + '/global.cat.scss',
        _package.dist_directory + '/global.min.css.map',
      ],
      options: {
        force: true,
      },
    },

    watch: {
      scss: {
        files: _package.build_directory + '/scss/*.scss',
        tasks: ['concat:scss', 'sass:main', 'postcss', 'clean:scss'],
        options: {
          interrupt: true,
          livereload: true,
        },
      },
      js: {
        files: _package.build_directory + '/js/*.js',
        tasks: ['concat:js', 'uglify:main', 'clean:js'],
      },
      pot: {
        files: _package.src_directory + '/**/*.php',
        tasks: ['pot'],
      },
    },
  });

  grunt.loadNpmTasks('grunt-pot');
  grunt.loadNpmTasks('grunt-po2mo');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-sass');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-postcss');

  grunt.registerTask('compile',
      ['concat', 'uglify', 'sass', 'postcss', 'clean', 'pot']);//, 'po2mo']);
  grunt.registerTask('default',
      ['concat', 'uglify', 'sass', 'postcss', 'clean', 'pot', 'watch']);//, 'po2mo']);
};
