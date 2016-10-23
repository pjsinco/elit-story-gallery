<?php 
/*
Plugin Name: Elit Story Gallery
Plugin URI:  
Description: Lightboxed, responsive, tiled image gallery
Version:     0.0.1
Author: Patrick Sinco
Author URI: 
License: GPL2
*/


// if this file is called directly, abort
if (!defined('WPINC')) {
  die;
}

function elit_add_body_class ( $classes ) {
  $classes[] = 'hiya';
  return $classes;
}
function elit_story_gallery_shortcodes_init( ) {

  if ( !shortcode_exists( 'story-gallery' ) ) {

    function elit_story_gallery_shortcodes( $atts, $content = null ) {

      elit_enqueue();

      $atts = array_change_key_case( (array)$atts, CASE_LOWER );
      $shortcode_atts = shortcode_atts(
        array(
          'ids' => '',
          'max-height' => 200
        ),
        $atts
      );

      $markup  = '<div class="gallery google-image-layout" ';
      $markup .= 'data-google-image-layout data-max-height="' . $shortcode_atts['max-height'] . '">';

      foreach ( explode( ',', $shortcode_atts['ids'] ) as $thumb_id ) {

        //$max_width = $shortcode_atts['max-height'] / 2 * 3;
        $max_height = $shortcode_atts['max-height'];

        $small = 
          wp_get_attachment_image_src( $thumb_id, array( 0, $max_height ) );

        $small_url = $small[0];
        $full_url = wp_get_attachment_url( $thumb_id ); 

        $html = elit_figure_markup( $small_url, $full_url );

        $markup .= $html;
      }

      $markup .= '</div><!-- .gallery -->';

      return $markup;
    }

    add_shortcode( 'story-gallery', 'elit_story_gallery_shortcodes' );

    function elit_add_pswp_element( $content ) {

      $markup = <<<EOF
      <div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="pswp__bg"></div>
          <div class="pswp__scroll-wrap">
              <div class="pswp__container">
                  <div class="pswp__item"></div>
                  <div class="pswp__item"></div>
                  <div class="pswp__item"></div>
              </div>
      
              <div class="pswp__ui pswp__ui--hidden">
                  <div class="pswp__top-bar">
                      <div class="pswp__counter"></div>
                      <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
                      <button class="pswp__button pswp__button--share" title="Share"></button>
                      <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
                      <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
                      <div class="pswp__preloader">
                          <div class="pswp__preloader__icn">
                            <div class="pswp__preloader__cut">
                              <div class="pswp__preloader__donut"></div>
                            </div>
                          </div>
                      </div>
                  </div>
      
                  <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                      <div class="pswp__share-tooltip"></div> 
                  </div>
      
                  <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
                  </button>
      
                  <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
                  </button>
      
                  <div class="pswp__caption">
                      <div class="pswp__caption__center"></div>
                  </div>
              </div>
          </div>
      </div> <!-- .pswp -->
EOF;

      return $content . $markup;
    }

    /**
     * Enqueue scripts and stylesheets
     *
     */
    function elit_enqueue() {

      wp_enqueue_style(
        'elit-story-gallery-styles',
        plugins_url( 'public/styles/elit-story-gallery.css', __FILE__ ),
        array(),
        filemtime( plugin_dir_path(__FILE__) . '/public/styles/elit-story-gallery.css' )
      );

      wp_register_script(
        'elit-story-gallery-vendor-bundle',
        //plugins_url( 'public/scripts/elit-story-gallery.min.js', __FILE__ ),
        plugins_url( 'public/scripts/elit-story-gallery.js', __FILE__ ),
        array( 'jquery' ),
        filemtime( plugin_dir_path(__FILE__) . '/public/scripts/elit-story-gallery.min.js' ), 
        true
      );

      wp_enqueue_script(
        'elit-story-gallery-main',
        plugins_url( '/public/scripts/main.js', __FILE__ ),
        array( 'elit-story-gallery-vendor-bundle' ),
        filemtime( plugin_dir_path(__FILE__) . '/public/scripts/main.js' ), 
        true
      );
    }

    /**
     * Generate HTML for each figure
     *
     * @param string $small_url URL for the small image to display
     * @param string $full_url  URL for the full-size image
     * @return string Markup for the figure
     */
    function elit_figure_markup( $small_url, $full_url ) {

      $dimensions = elit_parse_dimensions( basename( $small_url ) );
      $width = $dimensions['width'];
      $height = $dimensions['height'];

      $html  = '<figure>';
      $html .= "<a href=\"$full_url\">"; // 
      $html .= "<img src=\"$small_url\" data-width=\"$width\" data-height=\"$height\" class=\"gallery__img\" alt=\"\">";
      $html .= "</a>";
      $html .= "</figure>";
      
      return $html;
    }

    /**
     * Parse the width and height from a WordPress size-suffixed image filename, 
     * e.g. murthy-480x318.jpg 
     *
     * @param string $filename The filename to parse
     * @return false | Associative array with values for width, height
     */
    function elit_parse_dimensions( $filename ) {

      $re = '/\d{3}x\d{3}/';

      $match = preg_match( $re, $filename, $matches );

      if ( $match ) {
        $vals = explode( 'x', $matches[0] );
        $keys = array( 'width', 'height' );
        return array_combine( $keys, $vals );
      }

      return false;
    }
  }
}
add_filter( 'the_content', 'elit_add_pswp_element', 10, 1 );
add_action('init' , 'elit_story_gallery_shortcodes_init' );
