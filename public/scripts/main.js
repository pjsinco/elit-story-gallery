(function($) {

  /**
   * Photoswipe Transitions
   *
   */

  var mouseUsed = false; 

  $('body')
    .on('mousedown', '.pswp__scroll-wrap', function(evt) {
      // On mousedown, temporarily remove the transition class 
      // in preparation for swipe.
      $(this).children('.pswp__container_transition').removeClass('pswp__container_transition');
    })
    .on('mousedown', 
        '.pswp__button--arrow--left, .pswp__button--arrow--right', 
        function (evt) {
          // Exclude navigation arrows from above event
          evt.stopPropagation();
        }
    )
    .on('mousemove.detect', function(evt) {
      mouseUsed = true;
      $('body').off('mousemove.detect');
    });


  function debounce(f, wait, immediate) {
    var timeout;
    return function() {
      var context = this;
      var args = arguments;
      var later = function() {
        timeout = null;
        if (!immediate) {
          f.apply(context, args);
        }
      };
      var callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) {
        f.apply(context, args);
      }
    };
  }

  function initGallery() {
    var $pswp = $('.pswp')[0];
    var image = [];


    $('.gallery').each(function() {

      var $gallery = $(this);

      var getItems = function() {
        var items = [];
        $gallery.find('a').each(function() {
          var href = $(this).attr('href');

          var item = {
            src: href,
            w: 992,
            h: 661
            //w: $(this).find('img').data('width'),
            //h: $(this).find('img').data('height'),
          };

          items.push(item);
        });

        return items;
      };

      var items = getItems();

      $gallery.on('click', 'figure', function(evt) {
        evt.preventDefault();

        var $index = $(this).index() - 1; 
        // -1 to account for empty .gallery__sizer

        var options = {
          index: $index,
          bgOpacity: 0.9,
          showHideOpacity: true,
        };
  
        var gallery = new PhotoSwipe($pswp, PhotoSwipeUI_Default, items, options );

        // Triggers only on mouseUsed
        function transitionManager() {
          var currentSlide = options.index;

          // Listen for photoswipe change event to re-apply transition class
          gallery.listen('beforeChange', function() {

            // Only apply transition class if difference between last and next 
            // slide is < 2.
            // If it is > 1, it means we're at a loop seam.
            var transition = 
              Math.abs(gallery.getCurrentIndex() - currentSlide) < 2;

            $('.pswp__container')
              .toggleClass('pswp__container_transition', transition);

            currentSlide = gallery.getCurrentIndex();
          });
        }

        // Only apply transition manager functionality if we have a mouse
        if (mouseUsed) {
          transitionManager();
        } else {
          gallery.listen('mouseUsed', function() {
            mouseUsed = true;
            transitionManager();
          });
        }

        gallery.init();
      });

    });
  }


  var $gallery = $('.gallery').imagesLoaded(function() {

    $gallery.masonry({
      itemSelector: '.gallery__item',
      columnWidth: '.gallery__sizer',
      percentPosition: true,
    });

    initGallery();
    
  });


})(jQuery);

