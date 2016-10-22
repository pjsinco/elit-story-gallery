var files = {

  vendor: [
    'vendor/imagesloaded.pkgd.min.js',
    'vendor/google-image-layout-modified.js',
    'vendor/photoswipe-ui-default.min.js',
    'vendor/photoswipe.js',
  ]
};

module.exports = function(grunt) {


  grunt.initConfig({

    pkg: grunt.file.readJSON('package.json'),

    concat: {
      options: {
        //stripBanners: true,
      },
      dist: {
        src: files.vendor,
        //src: ['vendor/**/*.js'],
        dest: './public/scripts/<%= pkg.name %>.js',
        //dest: 'public/scripts/elit-story-gallery-bundle.js',
      },
    },

    uglify: {
      options: {
        banner: '/* <%= pkg.name %> - v<%= pkg.version %> */'
      },
      dist: {
        files: {
          './public/scripts/<%= pkg.name %>.min.js': ['<%= concat.dist.dest %>']
        }
      }
    },

  });

  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify');

  grunt.registerTask('default', ['concat', 'uglify']);
};
