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