// // AYU OFF CANVAS 

$(document).ready(function(){
  var siteWrapper = $('#site-wrapper'),
      siteMenu    = $('#site-menu'),
      siteCanvas  = $('#site-canvas');

  siteMenu.hover(function(){
    if (!siteWrapper.hasClass('homepage-on')) {
      siteWrapper.addClass('show-nav');
    }
  }, function(){
    siteWrapper.removeClass('show-nav');
  });
});

// (function() {

//     if(!WURFL.is_mobile) {
//       $('#site-menu').addClass("toggle-nav");
//     }

//     $(document).ready(function(){
//       var $menuToggle = $('.toggle-nav'),
//           $hoverToggle =  $('.hover-toggle'),
//           $linkToggle = $('.toggle-nav-link');
//       $linkToggle.on('click touchstart', toggleNav);
//       $menuToggle.on('mouseenter', showNav);
//       $menuToggle.on('mouseleave', hideNav);
//       $hoverToggle.on('mouseenter', hovered);
//       $hoverToggle.on('mouseleave', unhovered);
//     });

// })();


// ========================================
// =            CUSTOM FUNCTIONS            =
// ========================================
// function toggleNav(e) {
//   e.preventDefault();
//   $('#site-wrapper').toggleClass('show-nav');
//   $('#site-canvas').toggleClass('opened');
// }

// function showNav() {
//   $('#site-wrapper').addClass('show-nav');
//   $('#site-canvas').addClass('opened');
// }

// function hideNav() {
//   $('#site-wrapper').removeClass('show-nav');   
// }

// function hovered(){
//   $(this).addClass('hovered');
// }

// function unhovered(){
//   $(this).removeClass('hovered');
// }