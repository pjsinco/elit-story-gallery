<?php 

function elit_story_gallery_shortcodes_init( ) {

  if ( !shortcode_exists( 'elit_story_gallery' ) ) {

    function elit_story_gallery_shortcodes( $atts, $content = null ) {

      $atts = array_change_key_case( (array)$atts, CASE_LOWER );
      $shortcode_atts = shortcode_atts(
        array(
          'ids' => '',
        ),
        $atts
      );

      $markup  = '<div class="gallery google-image-layout" data-google-image-layout data-max-height="150">';

      return $content;
    }

    add_shortcode( 'elit-story-gallery', 'elit_story_gallery_shortcodes' );
    
  }
}

add_action('init' , 'elit_story_gallery_shortcodes_init');

