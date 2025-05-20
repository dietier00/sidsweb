(function ($) {
  
  "use strict";

    // HERO SLIDE
    const images = [
      "images/home/blinds5.jpeg",
      "images/home/blinds6.jpeg",
      "images/home/blinds7.jpeg",
      "images/home/blinds8.jpeg",
      "images/home/blinds9.jpeg",
      "images/home/blinds10.jpeg",
      "images/home/blinds11.jpeg",
      "images/home/blinds12.jpeg",
      "images/home/blinds13.jpeg",
    ];
    let currentIndex = 0;

    function changeBackground(index) {
        $('.hero-slide').css({
            'background-image': `url(${images[index]})`,
            'background-size': 'contain', // Ensure the image fits the content
            'background-repeat': 'no-repeat', // Prevent tiling
            'background-position': 'center' // Center the image
        });
    }

    function showNextImage() {
        currentIndex = (currentIndex + 1) % images.length;
        changeBackground(currentIndex);
    }

    function showPreviousImage() {
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        changeBackground(currentIndex);
    }

    // Initialize the first image
    changeBackground(currentIndex);

    // Add event listeners for navigation arrows
    $('.hero-slide-next').click(showNextImage);
    $('.hero-slide-prev').click(showPreviousImage);

    setInterval(showNextImage, 3000); 

    // REVIEWS CAROUSEL
    $('.reviews-carousel').owlCarousel({
      items:3,
      loop:true,
      dots: false,
      nav: true,
      margin:30,
      responsive:{
        0:{
          items:1
        },
        600:{
          items:2
        },
        1000:{
          items:3
        }
      }
    });

    // CUSTOM LINK
    $('.smoothscroll').click(function(){
      var el = $(this).attr('href');
      var elWrapped = $(el);
      var header_height = $('.navbar').height();

      scrollToDiv(elWrapped,header_height);
      return false;

      function scrollToDiv(element,navheight){
        var offset = element.offset();
        var offsetTop = offset.top;
        var totalScroll = offsetTop-navheight;

        $('body,html').animate({
          scrollTop: totalScroll
        }, 300);
      }
    });
    
})(window.jQuery);