<?php 
/*
Plugin Name: Elit Story Gallery
Plugin URI:  
Description: Lightboxed, responsive, tiled image gallery
Version:     0.4.0
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
          'columns' => '3',
          'thumb-max-width' => 331,
          //'full-max-width' => 992,
        ),
        $atts
      );

      $stylewidth = elit_stylewidth( $shortcode_atts['columns'] );

      $markup = '<div class="gallery">';
      // Masonry wants a blank first item
      $markup .= '<figure class="gallery__sizer"></figure>'; 

      $largest_image_name = elit_get_largest_image_name();

      foreach ( explode( ',', $shortcode_atts['ids'] ) as $image_id ) {

        //$max_width = $shortcode_atts['max-height'] / 2 * 3;
        $thumb_max_width = $shortcode_atts['thumb-max-width'];
        $full_max_width = $shortcode_atts['thumb-max-width'];

        $thumb = 
          wp_get_attachment_image_src( $image_id, array( $thumb_max_width ) );
        $full_size = wp_get_attachment_image_src( $image_id, 'elit-super' );
        $content = get_post( $image_id );

        if ( !$content || !$thumb || !$full_size ) {
          continue;
        }
    
        $srcset = wp_get_attachment_image_srcset( $image_id, array($thumb_max_width) ); 
        $html = elit_figure_markup( $thumb, 
                                    $full_size, 
                                    $srcset, 
                                    $content->post_excerpt,
                                    $stylewidth );

        $markup .= $html;
      }

      $markup .= '</div><!-- .gallery -->';

      return $markup;
    }

    add_shortcode( 'story-gallery', 'elit_story_gallery_shortcodes' );

    /**
     * @param string $columns The number of columns
     * @param string          A style rule for for width
     */
    function elit_stylewidth( $columns ) {

      $width = round( ( 100 / (int) $columns ), 1 ) - .1;

      return sprintf( 'width: %f%s', $width, '%' );
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
        array( 'jquery', 'jquery-masonry' ),
        //filemtime( plugin_dir_path(__FILE__) . '/public/scripts/elit-story-gallery.min.js' ), 
        filemtime( plugin_dir_path(__FILE__) . '/public/scripts/elit-story-gallery.js' ), 
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
     * @param array $thumb       For small image, the array returned from 
                                 wp_get_attachment_image_src()
     * @param array $full_size   For full image, the array returned from 
                                 wp_get_attachment_image_src()
     * @param string $srcset     The srcset attribute
     * @param string $caption    The caption to display
     * @param string $stylewidth A fraction expressed as words, e.g., 'one-third'
     * @return string Markup for the figure
     */
    function elit_figure_markup( $thumb, $full_size, $srcset, $caption, $stylewidth ) {

      $thumb_url = $thumb[0];
      $thumb_width = $thumb[1];
      $thumb_height = $thumb[2];

      $full_size_url = $full_size[0];
      $full_size_width = $full_size[1];
      $full_size_height = $full_size[2];

      //$dimensions = elit_parse_dimensions( basename( $thumb_url ) );
      //$width = $dimensions['width'];
      //$height = $dimensions['height'];

      $html  = '<figure class="gallery__item" style="' . $stylewidth . ';">';
      $html .= '<a href="' . esc_url( $full_size_url ) . '"'; 
      $html .= ' data-width="' . $full_size_width .'"';
      $html .= ' data-height="' . $full_size_height . '"';
      $html .= ' data-caption="' . esc_attr( $caption ) . '">';
      $html .= '<img alt="" src="' . esc_url( $thumb_url ) . '" style="max-width: none;"';
      $html .= 'srcset="' . esc_attr( $srcset ) . '" />';
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
     * Get size information for all currently-registered image sizes.
     *
     * source: https://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
     * @global $_wp_additional_image_sizes
     * @uses   get_intermediate_image_sizes()
     * @return array $sizes Data for all currently-registered image sizes.
     */
    function elit_get_image_sizes() {
    	global $_wp_additional_image_sizes;
    
    	$sizes = array();
    
    	foreach ( get_intermediate_image_sizes() as $_size ) {
    		if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
    			$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
    			$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
    			$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
    		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
    			$sizes[ $_size ] = array(
    				'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
    				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
    				'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
    			);
    		}
    	}
    
    	return $sizes;
    }

    /**
     * Get size information for a specific image size.
     *
     * @uses   elit_get_image_sizes()
     * @param  string $size The image size for which to retrieve data.
     * @return bool|array $size Size data about an image size or false if the size doesn't exist.
     */
    function get_image_size( $size ) {
    	$sizes = elit_get_image_sizes();
    
    	if ( isset( $sizes[ $size ] ) ) {
    		return $sizes[ $size ];
    	}
    
    	return false;
    }
    
    /**
     * Get the width of a specific image size.
     *
     * @uses   get_image_size()
     * @param  string $size The image size for which to retrieve data.
     * @return bool|string $size Width of an image size or false if the size doesn't exist.
     */
    function get_image_width( $size ) {
    	if ( ! $size = get_image_size( $size ) ) {
    		return false;
    	}
    
    	if ( isset( $size['width'] ) ) {
    		return $size['width'];
    	}
    
    	return false;
    }
    
    /**
     * Get the height of a specific image size.
     *
     * @uses   get_image_size()
     * @param  string $size The image size for which to retrieve data.
     * @return bool|string $size Height of an image size or false if the size doesn't exist.
     */
    function get_image_height( $size ) {
    	if ( ! $size = get_image_size( $size ) ) {
    		return false;
    	}
    
    	if ( isset( $size['height'] ) ) {
    		return $size['height'];
    	}
    
    	return false;
    }

    /**
     * Get the name of the largest image size available.
     * @uses elit_get_image_sizes()
     * @return string The name of the largest image size
     */
    function elit_get_largest_image_name() {

      $image_sizes = elit_get_image_sizes();
      $keys = array_keys($image_sizes);
      $widest = 0;
      $widest_name = null;

      for ($i = 0; $i < count($keys); $i++) {
//echo '<pre>'; var_dump($keys[$i], ($image_sizes[$keys[$i]]['width']), $widest_name, $widest, $image_sizes[$keys[$i]]['width'] > $widest ); echo '</pre>'; 
        if ($image_sizes[$keys[$i]]['width'] > $widest) {
      
          $widest = $image_sizes[$keys[$i]]['width'];
          $widest_name = $keys[$i];
        }
      }

      return $widest_name;
    }
  }
}
add_filter( 'the_content', 'elit_add_pswp_element', 10, 1 );
add_action('init' , 'elit_story_gallery_shortcodes_init' );
