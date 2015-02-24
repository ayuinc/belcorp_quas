/*!
 * EBM Gruntfile
 * http://soygus.com
 * @author MadeByGus (GIT: netpoe)
 */

'use strict';

/**
 * Livereload and connect variables
 */
var LIVERELOAD_PORT = 35729;
var lrSnippet = require('connect-livereload')({
  port: LIVERELOAD_PORT
});
var mountFolder = function (connect, dir) {
  return connect.static(require('path').resolve(dir));
};

/**
 * Grunt module
 */
module.exports = function (grunt) {

  /**
   * Dynamically load npm tasks
   */
  require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

  /**
   * FireShell Grunt config
   */
  grunt.initConfig({

    pkg: grunt.file.readJSON('package.json'),

    /**
     * Set project info
     */
    project: {
      src: 'src',
      app: 'app',
      assets: '<%= project.app %>/assets',
      css: [
        '<%= project.src %>/scss/style.scss'
      ],
      ebm: [
        '<%= project.src %>/scss/ebm.scss'
      ],
      js: [
        '<%= project.src %>/js/lib/*.js'
      ],
      coffee: [
        '<%= project.src %>/coffee/*.coffee'
      ]
    },

    /**
     * Project banner
     * Dynamically appended to CSS/JS files
     * Inherits text from package.json
     */
    tag: {
      banner: '/*!\n' +
              ' * <%= pkg.name %>\n' +
              ' * <%= pkg.title %>\n' +
              ' * <%= pkg.url %>\n' +
              ' * @author <%= pkg.author %>\n' +
              ' * @version <%= pkg.version %>\n' +
              ' * Copyright <%= pkg.copyright %>. <%= pkg.license %> licensed.\n' +
              ' */\n'
    },

    /**
     * Connect port/livereload
     * https://github.com/gruntjs/grunt-contrib-connect
     * Starts a local webserver and injects
     * livereload snippet
     */
    connect: {
      options: {
        port: 9000,
        hostname: '*'
      },
      livereload: {
        options: {
          middleware: function (connect) {
            return [lrSnippet, mountFolder(connect, 'app')];
          }
        }
      }
    },

    /**
     * Clean files and folders
     * https://github.com/gruntjs/grunt-contrib-clean
     * Remove generated files for clean deploy
     */
    clean: {
      dist: [
        '<%= project.assets %>/css/style.pkgd.min.css'
      ]
    },

    /**
     * Compile COFFEESCRIPT files
     * https://github.com/gruntjs/grunt-contrib-coffee
     * Compiles all COFFEESCRIPT files
     */

    coffee: {
      dev: {
        files: {
          '<%= project.src %>/js/lib/coffeeCompile.js': '<%= project.coffee %>'
        }
      }
    },

    /**
     * JSHint
     * https://github.com/gruntjs/grunt-contrib-jshint
     * Manage the options inside .jshintrc file
     */
    // jshint: {
    //   files: [
    //     'src/js/{,*/}*/{,*/}*.js',
    //     'Gruntfile.js'
    //   ],
    //   options: {
    //     jshintrc: '.jshintrc'
    //   }
    // },

    /**
     * Concatenate JavaScript files
     * https://github.com/gruntjs/grunt-contrib-concat
     * Imports all .js files and appends project banner
     */
    // concat: {
    //   dev: {
    //     files: {
    //       '<%= project.src %>/js/scripts.min.js': '<%= project.js %>'
    //     }
    //   },
    //   options: {
    //     stripBanners: true,
    //     nonull: true,
    //     banner: '<%= tag.banner %>'
    //   }
    // },

    /**
     * Uglify (minify) JavaScript files
     * https://github.com/gruntjs/grunt-contrib-uglify
     * Compresses and minifies all JavaScript files into one
     */
    uglify: {
      options: {
        banner: '<%= tag.banner %>'
      },
      dist: {
        files: {
          // '<%= project.assets %>/js/scripts.min.js': '<%= project.js %>', 
          '<%= project.assets %>/js/scripts.min.js': [
            // '<%= project.src %>/js/lib/TweenMax.min.js',
            // '<%= project.src %>/js/lib/ScrollToPlugin.min.js',
            // '<%= project.src %>/js/lib/jquery.scrollmagic.min.js',
            // '<%= project.src %>/js/lib/imagesloaded.pkgd.min.js',
            // '<%= project.src %>/js/lib/isotope.pkgd.min.js',
            // '<%= project.src %>/js/lib/flickity.pkgd.min.js',
            // '<%= project.src %>/js/lib/transformicons.js',
            '<%= project.src %>/js/lib/coffeeCompile.js'
          ]
        }
      }
    },

    /**
     * Compile Sass/SCSS files
     * https://github.com/gruntjs/grunt-contrib-sass
     * Compiles all Sass/SCSS files and appends project banner
     */
    sass: {
      ebm: {
        options: {
          style: 'expanded',
          banner: '<%= tag.banner %>'
        },
        files: {
          '<%= project.assets %>/css/ebm.css': '<%= project.ebm %>'
        }
      },
      dev: {
        options: {
          style: 'expanded',
          banner: '<%= tag.banner %>'
        },
        files: {
          '<%= project.assets %>/css/style.css': '<%= project.css %>'
        }
      },
      dist: {
        options: {
          style: 'expanded'
        },
        files: {
          '<%= project.assets %>/css/style.pkgd.min.css': '<%= project.src %>/scss/style.pkgd.scss'
          // '<%= project.assets %>/css/style.unprefixed.css': '<%= project.css %>'
        }
      }
    },    

    /**
     * Autoprefixer
     * Adds vendor prefixes automatically
     * https://github.com/nDmitry/grunt-autoprefixer
     */
    autoprefixer: {
      options: {
        browsers: [
          'last 2 version',
          'safari 6',
          'ie 9',
          'opera 12.1',
          'ios 6',
          'android 4'
        ]
      },
      dev: {
        files: {
          '<%= project.assets %>/css/style.min.css': ['<%= project.assets %>/css/style.unprefixed.css']
        }
      },
      dist: {
        files: {
          '<%= project.assets %>/css/style.prefixed.css': ['<%= project.assets %>/css/style.unprefixed.css']
        }
      }
    },

    /**
     * CSSMin
     * CSS minification
     * https://github.com/gruntjs/grunt-contrib-cssmin
     */
    cssmin: {
      // dev: {
      //   options: {
      //     banner: '<%= tag.banner %>'
      //   },
      //   files: {
      //     '<%= project.assets %>/css/style.min.css': [
      //       '<%= project.src %>/components/normalize-css/normalize.css',
      //       '<%= project.assets %>/css/style.unprefixed.css'
      //     ]
      //   }
      // },
      dist: {
        options: {
          banner: '<%= tag.banner %>'
        },
        files: {
          '<%= project.assets %>/css/style.min.css': [
            '<%= project.assets %>/css/style.pkgd.min.css'
          ]
        }
      }
    },

    /**
     * Build bower components
     * https://github.com/yatskevich/grunt-bower-task
     */
    bower: {
      dev: {
        install: {
          options: {
            targetDir: '<%= project.assets %>/components/',
            cleanTargetDir: true,
            cleanBowerDir: true
          }
        }
      },
      dist: {
        install: {
          options: {
            targetDir: '<%= project.assets %>/components/',
            cleanTargetDir: true,
            cleanBowerDir: true
          }
        }
      }
    },

    /**
     * Opens the web server in the browser
     * https://github.com/jsoverson/grunt-open
     */
    open: {
      server: {
        path: 'http://localhost:<%= connect.options.port %>'
      }
    },

    /**
     * Runs tasks against changed watched files
     * https://github.com/gruntjs/grunt-contrib-watch
     * Watching development files and run concat/compile tasks
     * Livereload the browser once complete
     */
    watch: {
      // concat: {
      //   files: '<%= project.src %>/js/{,*/}*.js',
      //   tasks: ['concat:dev', 'jshint']
      // },
      uglify: {
        files: '<%= project.src %>/js/lib/*.js',
        tasks: ['uglify']
      },
      sass: {
        files: '<%= project.src %>/scss/{,*/}*/{,*/}*.{scss,sass}',
        tasks: ['sass:dev', 'autoprefixer:dev']
      },
      coffee: {
        files: '<%= project.src %>/coffee/*.coffee',
        tasks: 'coffee:dev'
      },
      livereload: {
        options: {
          livereload: LIVERELOAD_PORT
        },
        files: [
          '<%= project.app %>/{,*/}*.html',
          '<%= project.assets %>/css/*.css',
          '<%= project.assets %>/js/{,*/}*.js',
          '<%= project.assets %>/{,*/}*.{png,jpg,jpeg,gif,webp,svg}'
        ]
      }
    }
  });

  /**
   * Default task
   * Run `grunt` on the command line
   */
  grunt.registerTask('default', [
    // 'coffee:dev',
    // 'sass:ebm',
    // 'sass:dev',
    'bower:dev',
    // 'autoprefixer:dev',
    // 'cssmin:dev',
    // 'jshint',
    // 'concat:dev',
    // 'connect:livereload',
    // 'uglify',
    // 'open',
    // 'watch'
  ]);

  /**
   * Build task
   * Run `grunt build` on the command line
   * Then compress all JS/CSS files
   */
  grunt.registerTask('build', [
    'sass:dist',
    'bower:dist',
    // 'autoprefixer:dist',
    'cssmin:dist',
    'clean:dist',
    // 'jshint',
    'uglify'
  ]);

};
