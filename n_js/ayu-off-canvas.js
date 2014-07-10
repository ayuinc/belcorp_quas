// AYU OFF CANVAS 

var $menuToggle = $('.toggle-nav'),
    $hoverToggle =  $('.hover-toggle'),
    $linkToggle = $('.toggle-nav-link');

(function() {

    $(document).ready(function(){
      if($(window).width() < 1281) {
        $linkToggle.click(toggleNav);
      } else {
        $menuToggle.on('mouseenter', toggleNav);
        $menuToggle.on('mouseleave', toggleNav);
        $hoverToggle.on('mouseenter', hovered);
        $hoverToggle.on('mouseleave', unhovered);
      }
    });

})();


/*========================================
=            CUSTOM FUNCTIONS            =
========================================*/
function toggleNav() {  
  if ($('#site-wrapper').hasClass('show-nav')) {
    $('#site-wrapper').removeClass('show-nav');    
  } else {
    $('#site-wrapper').addClass('show-nav');
    $('#site-canvas').addClass('opened');
  }
}

function hovered(){
  $(this).addClass('hovered');
}

function unhovered(){
  $(this).removeClass('hovered');
}