// AYU OFF CANVAS 

var $hoverToggle = $('.hover-toggle'),
    $linkToggle = $('.toggle-nav-link');

$(function() {

  if ($(window).width() < 480) {
    $linkToggle.click(toggleNav);
  } else {
    $hoverToggle.on('mouseover', toggleNav);
    $hoverToggle.on('mouseleave', toggleNav);
    $hoverToggle.on('mouseover', hovered);
    $hoverToggle.on('mouseleave', unhovered);
  }

});


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











