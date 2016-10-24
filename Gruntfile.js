var files = {

  vendor: {
    scripts: [
      //'vendor/scripts/imagesloaded.pkgd.min.js',
      //'vendor/scripts/google-image-layout-modified.js',
      'vendor/scripts/photoswipe-ui-default.min.js',
      'vendor/scripts/photoswipe.js',
    ],
  },
};

module.exports = function(grunt) {


  grunt.initConfig({

    pkg: grunt.file.readJSON('package.json'),
  
    sass: {
      options: {
        sourceMap: true,
      },
      dev: {
        files: {
          'public/styles/<%= pkg.name %>.css': 'sass/style.scss',
        },
      },
    },

    concat: {
      options: {
          //stripBanners: true,
      },
      all: {
        src: files.vendor.scripts,
        dest: './public/scripts/<%= pkg.name %>.js',
      },
    },
  
    uglify: {
      options: {
        banner: '/* <%= pkg.name %> - v<%= pkg.version %> */'
      },
      dist: {
        files: {
          './public/scripts/<%= pkg.name %>.min.js': ['<%= concat.all.dest %>']
        }
      }
    },

    notify: {
      sass: {
        options: {
          title: 'Sass',
          message: 'Sassed!',
        },
      },
      scripts: {
        options: {
          title: 'Scripts',
          message: 'Processed!',
        }
      }
    },

    watch: {
      scripts: {
        files: ['vendor/scripts/**/*.js'],
        tasks: ['concat', 'notify:scripts'],
      },
      sass: {
        files: ['sass/**/*.scss'],
        tasks: ['sass:dev', 'notify:sass'],
      },
    },

  });

  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-notify');

  grunt.registerTask('scripts-dev', ['concat']);
  grunt.registerTask('scripts-dist', ['concat', 'uglify']);
  grunt.registerTask('compile-sass', ['sass:dev', 'notify:sass']);
  grunt.registerTask('default', ['watch']);
};
